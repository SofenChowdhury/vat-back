import React, { useState, useRef} from "react";
import Authenticated from "@/Layouts/Authenticated";
import { Link, Head, usePage, useForm } from "@inertiajs/inertia-react";
import PageTitle from "@/Components/PageTitle";
import DateFormat from "@/Components/DateFormat";
import PaymentModal from "@/Components/PaymentModal";
import AdPaymentModal from "@/Components/AddPaymentModal";
import Status from "@/Components/order/Status";

export default function Index(props) {
    const { order } = usePage().props;
    const { data, setData, post, processing, errors, reset } = useForm({
        order_status: '',
        id: order.id
    });
    const orderItems = order.order_item;

    const handleChangeStatus = (event) => {
        if(order.order_status == event.target.value){
            alert("Already Selected");
            return false;
        }else if (order.order_status == 'delivered' || order.order_status == 'declined') {
            alert("Delivered or Declined Order Status not be Change");
            return false;
        }
        data.order_status = event.target.value;
        post(route('update.order'));
        
    };
    let back = function()
    {
        window.history.back();
    }
    const orderPrint = (e) => {
        window.open(route('order.print',order.id), '_blank', 'noopener,noreferrer');
    };
    return (
        <Authenticated auth={props.auth} errors={props.errors}>
            <Head title="Order" />
            <PageTitle>Order Details</PageTitle>
            
            
            <div className="grid-rows-1">
                <Link  onClick={back} className="bg-yellow-300 hover:bg-yellow-400 text-black font-bold py-2 px-4 border border-yellow-700 rounded w-24 mb-5 text-base float-left"> 
                    <i className="fas fa-chevron-left"></i> Back
                </Link>
                <Link onClick={orderPrint} className="bg-teal-300 hover:bg-teal-400 text-black py-2 px-4 border border-teal-700 rounded w-24 mb-5 text-base float-right"> 
                    <i className="fas fa-print"></i> Print
                </Link>
            </div>
            <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8 drop-shadow-lg">
                <div class="w-full overflow-x-auto">
                    <table className="w-full border-collapse border border-slate-400">    
                        <tbody>
                            <tr className="font-semibold tracking-wide text-left text-gray-500 capitalize border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <th className="px-4 py-3 text-right">Order Number</th>
                                <td className="px-4 py-3">: {order.order_number}</td>
                                <th className="px-4 py-3 text-right">Order Amount</th>
                                <td className="px-4 py-3">: {order.total_amount.toLocaleString()}</td>                                                                        
                            </tr>
                            <tr className="font-semibold tracking-wide text-left text-gray-500 capitalize border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                
                                <th className="px-4 py-3 text-right">Order Date</th>
                                <td className="px-4 py-3 ">: 
                                    <DateFormat date={order.created_at} dateFormat={'dd MMM yyyy h:mm aa'}></DateFormat>
                                </td>
                                <th className="px-4 py-3 text-right">Company</th>
                                <td className="px-4 py-3">: {order.company?.name}</td>
                                                                        
                            </tr>
                            <tr className="font-semibold tracking-wide text-left text-gray-500 capitalize border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">                                       
                                <th className="px-4 py-3 text-right">Customer Name</th>
                                <td className="px-4 py-3 ">: {order.customer_name}</td>
                                <th className="px-4 py-3 text-right">Branch</th>
                                <td className="px-4 py-3 ">: {order.shop?.title}</td>
                            </tr>
                            <tr className="font-semibold tracking-wide text-left text-gray-500 capitalize border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <th className="px-4 py-3 text-right">Customer Mobile</th>
                                <td className="px-4 py-3 ">: {order.customer_phone}</td>
                                <th className="px-4 py-3 text-right">City </th>
                                <td className="px-4 py-3 ">: {order.customer_city}</td>
                            </tr>
                            <tr className="font-semibold tracking-wide text-left text-gray-500 capitalize border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <th className="px-4 py-3 text-right">Order Status</th>
                                <td className="px-4 py-3 ">: 
                                    <Status status={order.order_status}></Status>                                    
                                </td>       
                                <th className="px-4 py-3 text-right">Customer Thana</th>
                                <td className="px-4 py-3 ">: {order.customer_thana}</td>                       
                            </tr>
                            <tr className="font-semibold tracking-wide text-left text-gray-500 capitalize border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <th className="px-4 py-3 text-right">Change Status</th>
                                <td className="px-4 py-3 ">
                                    <form method="get">
                                        <select name="order_status" onChange={handleChangeStatus} className="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                            <option value="">Select Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="confirmation">
                                                Confirmation
                                            </option>
                                            <option value="sap_confirmation">SAP Confirmation (ISM)</option>                                                                                                      
                                            <option value="in_delivery">In Delivery (Logistic)</option>                                                     
                                            <option value="received">Received (Branch)</option>
                                            <option value="delivered">Delivered (Logistic)</option>
                                            <option value="declined">Declined (Operation - B2B)</option>
                                            <option value="hold">Hold (Operation - B2B)</option> 
                                            <option value="lost">Lost (Logistic)</option> 
                                            <option value="returned">Returned (Logistic)</option>
                                            <option value="unreached">Unreached (Operation - B2B)</option>
                                        </select>
                                    </form>
                                    
                                </td>                                                       
                                <th className="px-4 py-3 text-right">Address</th>
                                <td className="px-4 py-3 ">: {order.customer_address}</td>
                                                    
                            </tr>
                            
                            
                        </tbody>
                    </table>
                </div>
            </div>
            
            <PageTitle>Ordered Items</PageTitle>
            <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8 drop-shadow-lg">
                <div class="w-full overflow-x-auto">
                    <table className="w-full border-collapse border border-slate-400">                        
                        <tbody>
                            <tr className="font-semibold tracking-wide text-left text-gray-500 capitalize border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <th className="px-4 py-3 text-left">Item ID</th>
                                <th className="px-4 py-3 text-left">Item SKU</th>
                                <th className="px-4 py-3 text-left">Image</th>                                
                                <th className="px-4 py-3 text-left">Item Title</th>
                                <th className="px-4 py-3 text-left">Details</th>
                                <th className="px-4 py-3 text-right">Price</th>
                                <th className="px-4 py-3 text-right">QTY</th>                                        
                                <th className="px-4 py-3 text-right">Sub-total</th>
                                {/* <th className="px-4 py-3 text-center">Action</th> */}
                                                                        
                            </tr>
                            
                                {orderItems.map( (itemValue, index) => (
                                        <tr className="font-semibold tracking-wide text-left text-gray-500 border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                            <td className="px-4 py-3 text-lg text-left">{itemValue.item_info.id} </td>
                                            <td className="px-4 py-3 text-lg text-left"> {itemValue.item_info.sku} </td>
                                            <td className="px-4 py-3 text-lg text-left h-28">
                                                <img src={`/storage/thumbnails/${itemValue.item_info.photo}`} width={100}/>
                                            </td>
                                            <td className="px-4 py-3 text-lg text-left"> {itemValue.item_info.title} </td>
                                            <td className="px-4 py-3 text-lg text-left"> 
                                            
                                            </td>
                                            <td className="px-4 py-3 text-lg text-right"> {itemValue.item_price.toLocaleString()} </td>
                                            <td className="px-4 py-3 text-lg text-right"> {itemValue.qty} </td>
                                            <td className="px-4 py-3 text-lg text-right"> {(itemValue.item_price * itemValue.qty).toLocaleString()} </td>
                                            {/* <td className="px-4 py-3 text-center"> 
                                                <button className="px-4 py-2 text-sm bg-orange-500 border text-white font-bold uppercase  rounded-lg shadow hover:shadow-lg outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150"
                                                > Edit</button>
                                                <button className="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-lg active:bg-red-400 hover:bg-red-700 focus:outline-none shadow hover:shadow-lg focus:shadow-outline-purple"
                                                > Delete</button>
                                                
                                            </td> */}
                                        </tr>
                                    ))
                                }
                            
                        </tbody>
                    </table>
                </div>
            </div>
            <PageTitle>Approval History</PageTitle>
            <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8 drop-shadow-lg">
                <div class="w-full overflow-x-auto">
                    <table className="w-full whitespace-no-wrap">
                        <thead className="text-lg font-semibold tracking-wide text-left text-gray-500 capitalize border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <tr>
                                <th className="px-4 py-3 text-left">Approved By</th>
                                <th className="px-4 py-3 text-left">Status</th>
                                <th className="px-4 py-3 text-left">Note</th>
                                <th className="px-4 py-3 text-center">Approved At</th>
                                                                        
                            </tr>
                        </thead>
                        
                        <tbody>
                        {order.histories?.map( (history, index) => (
                            <tr className="font-semibold tracking-wide text-left text-gray-500 border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <td className="px-4 py-3 text-left">{history?.admin?.name}</td>
                                <td className="px-4 py-3 text-left">  
                                    <Status status={history.title}></Status>
                                </td>
                                <td className="px-4 py-3 text-left">{history.note}</td>
                                <td className="px-4 py-3 text-left">
                                <DateFormat date={history.created_at} dateFormat={'dd-MMM-yyyy h:mm aa'}></DateFormat>
                                </td>
                            </tr>
                            ))
                        }
                        </tbody>
                    </table>
                </div>
            </div>
            <PaymentModal title={"Payment Information"} payments={order.payments}></PaymentModal>
        </Authenticated>
    );
}
