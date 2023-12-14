<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PDF Notes</title>

    <style>
        table td {
            /* font-family: Arial, Helvetica, sans-serif; */
            font-size: 14px;
        }
        table.data td,
        table.data th {
            border: 1px solid #ccc;
            padding: 5px;
        }
        table.data {
            border-collapse: collapse;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <table width="100%">
        <tr>
            <td rowspan="4" width="60%">
                <img src="{{ public_path($setting->path_logo) }}" alt="{{ $setting->path_logo }}" width="120">
                <br>
                {{ $setting->address }}
                <br>
                <br>
            </td>
            <td>Date</td>
            <td>: {{ tanzanian_date(date('Y-m-d')) }}</td>
        </tr>
        <tr>
            <td>Member Code</td>
            <td>: {{ $sales->member->member_code ?? '' }}</td>
        </tr>
    </table>

    <table class="data" width="100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Code</th>
                <th>Name</th>
                <th>Unit Price</th>
                <th>Quantity</th>
                <th>Discount</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detail as $key => $item)
                <tr>
                    <td class="text-center">{{ $key+1 }}</td>
                    <td>{{ $item->product->product_name }}</td>
                    <td>{{ $item->product->product_code }}</td>
                    <td class="text-right">{{ format_uang($item->selling_price) }}</td>
                    <td class="text-right">{{ format_uang($item->amount) }}</td>
                    <td class="text-right">{{ $item->discount }}</td>
                    <td class="text-right">{{ format_uang($item->subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right"><b>Total Price</b></td>
                <td class="text-right"><b>{{ format_uang($sales->total_price) }}</b></td>
            </tr>
            <tr>
                <td colspan="6" class="text-right"><b>Discount</b></td>
                <td class="text-right"><b>{{ format_uang($sales->discount) }}</b></td>
            </tr>
            <tr>
                <td colspan="6" class="text-right"><b>Total Pay</b></td>
                <td class="text-right"><b>{{ format_uang($sales->pay) }}</b></td>
            </tr>
            <tr>
                <td colspan="6" class="text-right"><b>Received</b></td>
                <td class="text-right"><b>{{ format_uang($sales->accepted) }}</b></td>
            </tr>
            <tr>
                <td colspan="6" class="text-right"><b>Return</b></td>
                <td class="text-right"><b>{{ format_uang($sales->accepted - $sales->pay) }}</b></td>
            </tr>
        </tfoot>
    </table>

    <table width="100%">
        <tr>
            <td><b>Thank you for shopping. We hope to see you again!</b></td>
            <td class="text-center">
                Cashier
                <br>
                <br>
                {{ auth()->user()->name }}
            </td>
        </tr>
    </table>
</body>
</html>