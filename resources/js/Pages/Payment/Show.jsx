import React, { useState, useRef} from "react";
import Authenticated from "@/Layouts/Authenticated";
import { Link, Head, usePage, useForm } from "@inertiajs/inertia-react";
import PageTitle from "@/Components/PageTitle";
import DateFormat from "@/Components/DateFormat";
import ApprovedPaymentModal from "@/Components/ApprovedPaymentModal";
import Status from "@/Components/order/Status";

export default function Index(props) {
    const { payment } = usePage().props;
    const [permissions, setPermissions] = useState(props.auth.permissions);
    
    const paymentOrders = payment.order_payments   

    let back = function()
    {
        window.history.back();
    }
    // 👇️ check if array contains object
    const isApprovePayment = permissions.some(permission => {
        if (permission.name === 'approve deny payment') {
            return true;
        }
        return false;
    });
    const paymentPrint = (e) => {
        window.open(route('payment.print',payment.id), '_blank', 'noopener,noreferrer');
    };
    return (
        <Authenticated auth={props.auth} errors={props.errors}>
            <Head title="Payment Details"/>
            <PageTitle>Payment Details</PageTitle>          
            
            <div className="grid-rows-1">
                <Link  onClick={back} className="bg-yellow-300 hover:bg-yellow-400 text-black font-bold py-2 px-4 border border-yellow-700 rounded w-24 mb-5 text-base float-left"> 
                    <i className="fas fa-chevron-left"></i> Back
                </Link>
                <Link onClick={paymentPrint} className="bg-teal-300 hover:bg-teal-400 text-black py-2 px-4 border border-teal-700 rounded w-24 mb-5 text-base float-right"> 
                    <i className="fas fa-print"></i> Print
                </Link>
            </div>
            
            <div className="row-auto">
                <PageTitle>Payment Information </PageTitle>
                {isApprovePayment && payment.payment_status == "0" &&
                <ApprovedPaymentModal payment={payment} title={"Make Approved / Deny"}></ApprovedPaymentModal>               
                }
            </div>
            
            
            <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8 drop-shadow-lg">
                <div class="w-full overflow-x-auto">
                    <table className="w-full border-collapse border border-slate-400">    
                        <tbody className="font-semibold tracking-wide text-left text-gray-500 border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <tr>
                                <th className="px-4 py-3 text-right">Bank</th>
                                <td className="px-4 py-3">: {payment.bank}</td> 
                                <th className="px-4 py-3 text-right">Payment Date</th>
                                <td className="px-4 py-3 ">: 
                                    <DateFormat date={payment.created_at} dateFormat={'dd MMM yyyy h:mm aa'}></DateFormat>
                                </td>                                                                                                       
                            </tr>
                            <tr>
                                <th className="px-4 py-3 text-right">Branch</th>
                                <td className="px-4 py-3">: {payment.branch}</td> 
                                <th className="px-4 py-3 text-right">Amount</th>
                                <td className="px-4 py-3">: {payment.amount_credit.toLocaleString()}</td>               
                            </tr>
                            <tr>
                                <th className="px-4 py-3 text-right">Account Number</th>
                                <td className="px-4 py-3 ">: {payment.account_number}
                                </td>
                                <th className="px-4 py-3 text-right">Payment Status</th>
                                <td className="px-4 py-3">: <Status status={payment.payment_status}></Status></td>
                            </tr> 
                            <tr>
                                <th className="px-4 py-3 text-right">Company</th>
                                <td className="px-4 py-3">: {payment.company.name}
                                </td>
                                <th className="px-4 py-3 text-right">Verified By </th>
                                <td className="px-4 py-3">: {payment.verified?.name+" ["+payment.verified?.email+"]"} </td>
                                
                            </tr>  
                            <tr>
                                <th className="px-4 py-3 text-right">Branch</th>
                                <td className="px-4 py-3 ">: {payment.shop.title}
                                </td>
                                <th className="px-4 py-3 text-right">Payment Slip</th>
                                <td className="px-4 py-3">:  <Link  href={payment.payment_slip}  className="text-blue-700"> 
                                         Payment Slip
                                    </Link>
                                </td>
                            </tr> 
                        </tbody>
                    </table>
                </div>
            </div>
            
            <PageTitle>Paid Orders</PageTitle>
            
            <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8 drop-shadow-lg">
                <div class="w-full overflow-x-auto">
                    <table className="w-full border-collapse border border-slate-400">                        
                        <tbody>
                            <tr className="font-semibold tracking-wide text-left text-gray-500 capitalize border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <th className="px-4 py-3 text-left">Order Number</th>
                                <th className="px-4 py-3 text-center">Order Date</th>
                                <th className="px-4 py-3 text-right">Amount</th>
                                <th className="px-4 py-3 text-center">Status</th>                               
                                                                        
                            </tr>
                            
                                {paymentOrders.map( (order, index) => (
                                        <tr className="font-semibold tracking-wide text-left text-gray-500 border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                            <td className="px-4 py-3 text-lg text-left"> 
                                                <Link  href={route('orders.show', order.order_info.id )} className="text-blue-700"> 
                                                {order.order_info.order_number} 
                                                </Link>
                                            </td>
                                            <td className="px-4 py-3 text-center">
                                                <DateFormat date={order.order_info.created_at} dateFormat={'dd MMM yyyy h:mm aa'}></DateFormat>
                                            </td>
                                            <td className="px-4 py-3 text-lg text-right"> 
                                            {order.order_info.total_amount.toLocaleString()}
                                            </td>
                                            <td className="px-4 py-3 text-lg text-center"> 
                                                <Status status={order.order_info.order_status}></Status>
                                            </td>                                            
                                        </tr>
                                    ))
                                }
                            
                        </tbody>
                    </table>
                </div>
            </div>
        
        </Authenticated>
    );
}
