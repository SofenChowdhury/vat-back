<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <style type="text/css">
        body {margin:0px !important; padding:0px !important; display:block !important; min-width:100% !important; width:100% !important; -webkit-text-size-adjust:none;}
        table, th, td {
            border: solid 1px #3b3b3b;
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 10px;
        }
        th, td{
            padding: 10px;
            text-align: left;
        }

  </style>
</head>
<body onload="window.print()">
    <div class="invoice-wrap">
            <div class="invoice__title">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="invoice__logo text-left">
                           <img src="{{ asset('storage/settings/'.$setting->logo_lg) }}" alt="{{$setting->title}}">
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <table style="border: 0; padding:0px width:100%; margin-top:20px;">
                <tr>
                    <th style="border: 0; padding:0px; width: 49%">
                        <table>
                            <tr>
                                <th colspan="2"><h3>Order Information</h3></th>
                            </tr>
                            <tr>
                                <th style="width: 40%">Order Number</th>
                                <td>{{ $order->order_number }}</td>
                            </tr>
                            <tr>
                                <th style="width: 40%">Order Date</th>
                                <td>{{ date("F j, Y, g:i A", strtotime($order->created_at)); }}</td>
                            </tr>
                            <tr>
                                <th style="width: 40%">Order Status</th>
                                <td>{{ $order->order_status }}</td>
                            </tr>
                            
                            <tr>
                                <th style="width: 40%">Company</th>
                                <td>{{ $order->user->company->name; }}</td>
                            </tr>
                            <tr>
                                <th style="width: 40%">Branch (Shop)</th>
                                <td>{{ $order->user->shop->title; }}</td>
                            </tr>
                            <tr>
                                <th style="width: 40%">Order Note</th>
                                <td>{{ $order->order_note }}</td>
                            </tr>
                        </table>
                    </th>
                    <th style="border: 0; padding:0px; width:49%;">
                        <table>
                            <tr>
                                <th colspan="2"><h3>Shipping Information</h3></th>
                            </tr>
                            <tr>
                                <th style="width: 40%">Name</th>
                                <td>{{ ucfirst($order->customer_name) }}</td>
                            </tr>
                            <tr>
                                <th style="width: 40%">Mobile Number</th>
                                <td>{{ $order->customer_phone; }}</td>
                            </tr>
                            <tr>
                                <th style="width: 40%">Email</th>
                                <td>{{ $order->customer_email }}</td>
                            </tr>
                            <tr>
                                <th style="width: 40%">City</th>
                                <td>{{ $order->customer_city }}</td>
                            </tr>
                            <tr>
                                <th style="width: 40%">Thana</th>
                                <td>{{ $order->customer_thana }}</td>
                            </tr>
                            <tr>
                                <th style="width: 40%">Address</th>
                                <td>{{ $order->customer_address }}</td>
                            </tr>
                        </table>
                    </th>
                </tr>
            </table>


                <div class="col-lg-12" style="margin-top: 20px;">
                    <div class="table-responsive">
                        <table>            
                            <tbody>
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th>Item</th>
                                    <th nowrap>Price</th>
                                    <th nowrap>Qty</th>
                                    <th nowrap>Sub-Total</th>
                                </tr>
                                @php
                                    $i = 0;
                                    $total = 0;
                                @endphp
                                @foreach($order->orderItem as $item)
                                    @php
                                        $i++;
                                        $total += $item->item_price*$item->qty;
                                    @endphp
                                    <tr>
                                        <th style="width: 5%">{{ $i }}</th>
                                        <th>{{ $item->name }}</th>
                                        <th>{{ $item->item_price }}</th>
                                        <th>{{ $item->qty }}</th>
                                        <th>{{ $item->item_price*$item->qty }}</th>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" style="text-align: right;">Total</th>
                                    <th>{{ $total }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
        </div>


</body>
</html>
