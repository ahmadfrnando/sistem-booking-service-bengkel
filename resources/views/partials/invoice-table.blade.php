<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end pe-4">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($booking->items as $item)
                    <tr>
                        <td class="ps-4">
                            {{ $item->name }}
                            @if ($item->is_additional) <span class="badge text-bg-light border">Tambahan</span> @endif
                        </td>
                        <td class="text-center">{{ $item->qty }}</td>
                        <td class="text-end pe-4">@rupiah($item->subtotal())</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="ps-4">Total</th>
                    <th class="text-end pe-4">@rupiah($booking->total_amount)</th>
                </tr>
                @foreach ($booking->payments->where('status', 'paid') as $payment)
                    <tr class="text-success">
                        <td colspan="2" class="ps-4">{{ $payment->typeLabel() }} ({{ $payment->methodLabel() }})</td>
                        <td class="text-end pe-4">- @rupiah($payment->amount)</td>
                    </tr>
                @endforeach
                <tr>
                    <th colspan="2" class="ps-4">Sisa Tagihan</th>
                    <th class="text-end pe-4">@rupiah($booking->remainingAmount())</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
