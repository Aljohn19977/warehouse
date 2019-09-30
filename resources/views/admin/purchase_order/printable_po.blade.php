<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice - #123</title>

    <style type="text/css">
        @page {
            margin: 0px;
        }
        
        body {
            margin: 0px;
        }
        
        * {
            font-family: Verdana, Arial, sans-serif;
        }
        
        a {
            color: #fff;
            text-decoration: none;
        }
        
        table {
            font-size: x-small;
        }
        
        tfoot tr td {
            font-weight: bold;
            font-size: x-small;
        }
        
        .invoice table {
            margin: 15px;
        }
        
        .invoice h3  {
            margin-left: 15px;
        }
        .invoice h4  {
            margin-left: 15px;
        }
        
        .information {
            background-color: #333a40;
            color: #FFF;
        }
        
        .information .logo {
            margin: 5px;
        }
        
        .information table {
            padding: 10px;
        }
    </style>

</head>

<body>

    <div class="information">
        <table width="100%">
            <tr>
                <td align="left" style="width: 40%;">
                    <h3>{{ $supplier->fullname }}</h3>
                    <pre>
Supplier ID : {{ $supplier->supplier_id }}
Company : {{ $supplier->company->name }}
Email Address : {{ $supplier->email }}
Address : {{ $supplier->address }}
Tel No. : {{ $supplier->tel_no }}
Mobile No. : {{ $supplier->mobile_no }}

</pre>

                </td>
                <td align="center">
                    <img src="http://allvectorlogo.com/img/2016/10/wellesley-information-services-wis-logo.png" alt="Logo" width="64" class="logo" />
                </td>
                <td align="right" style="width: 40%;">

                    <h3>WiS</h3>
                    <pre>
                    https://wms-avm.com

                    Email Address : wms_info@gmail.com
                    Address : Lucena City
                    Tel No. : 042-797-0526
                    Mobile No. : 09199450224
                </pre>
                </td>
            </tr>

        </table>
    </div>

    <br/>

    <div class="invoice">
        <h3>Purchase Order # {{ $purchase_order->purchase_order_id }}</h3>
        <h4>Order Date : {{ $purchase_order->order_date }}</h4>
        <table width="100%">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Tax Percentage</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase_order_items as $item)
                <tr>
                    <td>{{ $item->item->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price, 0 ) }}</td>
                    <td>{{ $item->tax }} %</td>
                    <td align="left">{{ number_format($item->subtotal, 0 ) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                    <td align="left">Total</td>
                    <td align="left" class="gray">{{ number_format($purchase_order->total, 0 ) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="information" style="position: absolute; bottom: 0;">
        <table width="100%">
            <tr>
                <td align="left" style="width: 50%;">
                    &copy; {{ date('Y') }} https://wms-avm.com - All rights reserved.
                </td>
                <td align="right" style="width: 50%;">
                    We Find Right For You.
                </td>
            </tr>

        </table>
    </div>
</body>

</html>