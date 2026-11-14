<?php

namespace Tests\Feature;

use App\Mail\BookingCompletedMail;
use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\Mechanic;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        config([
            'services.xendit.secret_key' => 'xnd_development_test',
            'services.xendit.webhook_token' => 'webhook-token',
        ]);

        Mail::fake();

        Http::fake([
            'api.xendit.co/v3/payment_requests' => fn (Request $request) => Http::response([
                'payment_request_id' => 'pr-'.$request['reference_id'],
                'reference_id' => $request['reference_id'],
                'status' => 'REQUIRES_ACTION',
                'actions' => [['type' => 'PRESENT_TO_CUSTOMER', 'descriptor' => 'QR_STRING', 'value' => '00020101021226QRISTEST']],
            ], 201),
            'api.xendit.co/v3/payment_requests/*' => Http::response(['status' => 'REQUIRES_ACTION']),
        ]);
    }

    public function test_full_booking_flow(): void
    {
        $services = Service::whereIn('name', ['Servis Ringan', 'Servis CVT'])->get();
        $date = today()->addDay();
        if ($date->isSunday()) {
            $date->addDay();
        }

        $this->get('/')->assertOk()->assertSee('Layanan Populer');
        $this->get('/layanan?type=matic')->assertOk()->assertSee('Servis CVT');

        $this->post('/booking', [
            'customer_name' => 'Andi',
            'customer_email' => 'andi@example.com',
            'vehicle_type' => 'matic',
            'vehicle_brand' => 'Honda',
            'vehicle_model' => 'Vario 125',
            'plate_number' => 'd 1234 abc',
            'booking_date' => $date->toDateString(),
            'time_slot' => '08:00-10:00',
            'services' => $services->pluck('id')->all(),
        ])->assertRedirect();

        $booking = Booking::firstOrFail();
        $this->assertSame(150000, $booking->estimated_total);
        $this->assertSame(45000, $booking->dp_amount);
        $this->assertSame('D 1234 ABC', $booking->plate_number);

        $dp = $booking->dpPayment;
        Http::assertSent(fn (Request $r) => $r->url() === 'https://api.xendit.co/v3/payment_requests'
            && $r['channel_code'] === 'QRIS' && $r['request_amount'] === 45000 && $r->hasHeader('api-version', '2024-11-11'));

        $this->get(route('booking.pay', $booking))->assertOk()->assertSee('<svg', false);

        $this->postJson('/webhooks/xendit', ['event' => 'payment.capture'], ['x-callback-token' => 'salah'])->assertForbidden();

        $webhook = ['event' => 'payment.capture', 'data' => ['payment_request_id' => $dp->xendit_id, 'reference_id' => $dp->reference_id, 'status' => 'SUCCEEDED', 'request_amount' => 45000]];
        $this->postJson('/webhooks/xendit', $webhook, ['x-callback-token' => 'webhook-token'])->assertOk();
        $this->postJson('/webhooks/xendit', $webhook, ['x-callback-token' => 'webhook-token'])->assertOk();

        $this->assertSame('confirmed', $booking->refresh()->status);
        Mail::assertSentCount(1);
        Mail::assertSent(BookingConfirmedMail::class, fn ($mail) => $mail->hasTo('andi@example.com')
            && count($mail->attachments()) === 1
            && str_contains($mail->render(), $booking->code));

        $this->get(route('booking.invoice', $booking))->assertOk()->assertHeader('Content-Type', 'application/pdf');

        $admin = User::first();
        $this->actingAs($admin);

        $this->get('/admin')->assertOk();
        $this->get(route('admin.bookings.show', $booking))->assertOk()->assertSee('Verifikasi Kedatangan');
        $this->post(route('admin.bookings.check-in', $booking), ['plate_number' => 'D 1234 ABC'])->assertRedirect();
        $this->assertSame('checked_in', $booking->refresh()->status);

        $this->post(route('admin.bookings.start', $booking), ['mechanic_id' => Mechanic::first()->id])->assertRedirect();
        $this->assertSame('in_service', $booking->refresh()->status);

        $item = $booking->items()->first();
        $this->get(route('admin.bookings.finish', $booking))->assertOk();
        $this->post(route('admin.bookings.finish.store', $booking), [
            'items' => [$item->id => ['price' => 80000]],
            'extras' => [['name' => 'Oli MPX 0.8L', 'qty' => 1, 'price' => 55000]],
        ])->assertRedirect(route('admin.bookings.payment', $booking));

        $booking->refresh()->load('payments');
        $this->assertSame('waiting_payment', $booking->status);
        $this->assertSame(210000, $booking->total_amount);
        $this->assertSame(165000, $booking->remainingAmount());

        $this->get(route('admin.bookings.payment', $booking))->assertOk();
        $this->post(route('admin.bookings.payment.qris', $booking))->assertRedirect();
        $this->get(route('admin.bookings.payment', $booking))->assertOk()->assertSee('<svg', false);

        $this->post(route('admin.bookings.payment.manual', $booking), ['method' => 'cash', 'received_amount' => 100000])->assertSessionHasErrors('received_amount');
        $this->post(route('admin.bookings.payment.manual', $booking), ['method' => 'cash', 'received_amount' => 200000])->assertRedirect();

        $booking->refresh()->load('payments');
        $this->assertSame('completed', $booking->status);
        $this->assertSame(0, $booking->remainingAmount());
        $this->assertSame(1, $booking->payments->where('type', 'final')->where('status', 'paid')->count());

        $this->get(route('booking.receipt', $booking))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        Mail::assertSentCount(2);
        Mail::assertSent(BookingCompletedMail::class, fn ($mail) => $mail->hasTo('andi@example.com') && count($mail->attachments()) === 2
            && str_contains($mail->render(), 'Service Selesai'));
    }

    public function test_slot_capacity_is_enforced(): void
    {
        $date = today()->addDay();
        if ($date->isSunday()) {
            $date->addDay();
        }
        $service = Service::first();

        $payload = fn ($i) => [
            'customer_name' => 'Customer '.$i,
            'customer_email' => 'customer'.$i.'@example.com',
            'vehicle_type' => 'matic',
            'vehicle_brand' => 'Yamaha',
            'vehicle_model' => 'NMAX',
            'plate_number' => 'B 100'.$i.' XY',
            'booking_date' => $date->toDateString(),
            'time_slot' => '10:00-12:00',
            'services' => [$service->id],
        ];

        foreach (range(1, config('bengkel.slot_capacity')) as $i) {
            $this->post('/booking', $payload($i))->assertSessionHasNoErrors();
        }

        $this->post('/booking', $payload(9))->assertSessionHasErrors('time_slot');
    }

    public function test_email_is_required(): void
    {
        $this->post('/booking', ['customer_name' => 'Tanpa Email'])->assertSessionHasErrors('customer_email');
    }

    public function test_admin_pages_require_login(): void
    {
        $this->get('/admin/bookings')->assertRedirect(route('admin.login'));
    }
}
