import React, { useState } from "react";
import Authenticated from "@/Layouts/Authenticated";
import { Link, Head, usePage, useForm } from "@inertiajs/inertia-react";
import Pagination from "@/Components/Common/Pagination";
import PageTitle from "@/Components/PageTitle";
import DateFormat from "@/Components/DateFormat";
import Status from "@/Components/order/Status";
import Button from "@/Components/Button";

export default function Index(props) {
    const [permissions, setPermissions] = useState(props.auth.permissions);
    const isShowCompany = permissions.some(permission => {
        if (permission.name === 'show company') {
            return true;
        }
        return false;
    });

    const {orders, companies, admins, shops} = usePage().props;
    const [editForm, setEditForm] = useState(null); 
    const { data, setData, post, processing, errors, reset } = useForm({
        order_number: "",
        customer_phone: "",
        company_id: "",
        shop_id: "",
        admin_id: "",
        order_date: "",
        order_status: ""
    });

    const onHandleChange = (event) => {
        setData(
            event.target.name,
            event.target.type === "checkbox"
                ? event.target.checked
                : event.target.value
        );
    };

    const searchOrder = (e) => {
        e.preventDefault();
        get(route("orders.search"));
    };

    const orderDownload = (e) => {
        e.preventDefault();
        window.open(route('order.download'), 'noopener,noreferrer');
    };
    if (!orders) return "No Product found!";
    return (
        <Authenticated auth={props.auth} errors={props.errors}>
            <Head title="Order List" />
            <PageTitle>Orders Search</PageTitle>
            <div className="mb-4 w-full rounded-lg bg-white p-4 shadow-md dark:bg-gray-800">
                <form onSubmit={searchOrder ? searchOrder : "" }>
                    <div className="mb-2 grid grid-cols-7 gap-2">
                    
                        <div className="flex flex-col">
                            <label for="order_number" className="mb-2 font-semibold">Order Number</label>
                            <input type="text" id="order_number" className="block w-full mt-1 border rounded border-gray-300 dark:border-gray-600 
                                    dark:bg-gray-700 focus:border-purple-400 focus:outline-none 
                                    focus:shadow-outline-purple dark:text-gray-300 
                                    dark:focus:shadow-outline-gray form-input" />
                        </div>
                        <div className="flex flex-col">
                            <label for="customer_phone" className="mb-2 font-semibold">Customer Mobile</label>
                            <input type="text" id="customer_phone" className="block w-full mt-1 border rounded border-gray-300 dark:border-gray-600 
                                    dark:bg-gray-700 focus:border-purple-400 focus:outline-none 
                                    focus:shadow-outline-purple dark:text-gray-300 
                                    dark:focus:shadow-outline-gray form-input" />
                        </div>
                        <div className="flex flex-col">
                            <label for="order_status" className="mb-2 font-semibold">Order Status</label>
                            <select name="order_status" id="order_status" className="block w-full mt-1 border rounded border-gray-300 dark:border-gray-600 
                                dark:bg-gray-700 focus:border-purple-400 focus:outline-none 
                                focus:shadow-outline-purple dark:text-gray-300 
                                dark:focus:shadow-outline-gray form-input">
                                    <option value="">Select Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="confirmation">
                                        Confirmation
                                    </option>
                                    <option value="sap_confirmation">SAP Confirmation</option>                                                                                                      
                                    <option value="in_delivery">In Delivery</option>                                                     
                                    <option value="received">Received</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="declined">Declined</option>
                                    <option value="hold">Hold</option> 
                                    <option value="lost">Lost</option> 
                                    <option value="returned">Returned</option>
                                    <option value="unreached">Unreached</option>                              

                            </select>
                        </div>
                        {isShowCompany &&
                        <div className="flex flex-col">
                            <label for="order_status" className="mb-2 font-semibold">Company</label>
                            <select name="order_status" id="order_status" className="block w-full mt-1 border rounded border-gray-300 dark:border-gray-600 
                                dark:bg-gray-700 focus:border-purple-400 focus:outline-none 
                                focus:shadow-outline-purple dark:text-gray-300 
                                dark:focus:shadow-outline-gray form-input">
                                    <option value="">Select Company</option> 
                                    {companies && companies.map((value, index) => (
                                        <option value={value.id}>{value.name}</option>
                                    ))}                        

                            </select>
                        </div>
                        }
                        <div className="flex flex-col">
                            <label for="order_status" className="mb-2 font-semibold">Select Branch</label>
                            <select name="order_status" id="order_status" className="block w-full mt-1 border rounded border-gray-300 dark:border-gray-600 
                                dark:bg-gray-700 focus:border-purple-400 focus:outline-none 
                                focus:shadow-outline-purple dark:text-gray-300 
                                dark:focus:shadow-outline-gray form-input">
                                    <option value="">Select Branch</option> 
                                    {shops && shops.map((value, index) => (
                                        <option value={value.id}>{value.title}</option>
                                    ))}                        

                            </select>
                        </div>
                        <div className="flex flex-col">
                            <label for="order_date" className="mb-2 font-semibold">Order Date</label>
                            
                            <input type="date" id="order_date" className="block w-full mt-1 border rounded border-gray-300 dark:border-gray-600 
                                    dark:bg-gray-700 focus:border-purple-400 focus:outline-none 
                                    focus:shadow-outline-purple dark:text-gray-300 
                                    dark:focus:shadow-outline-gray form-input" />
                        </div>
                        <div className="flex flex-auto">
                            <label for="order_date" className="mb-7 font-semibold"></label>
                            <Button type="button" className="border border-indigo-500 bg-indigo-500 text-white rounded-md px-4 py-2 m-2 select-none hover:bg-indigo-600 focus:outline-none focus:shadow-outline w-16 h-11 mt-9">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </Button>
                            <Link onClick={orderDownload} className="border border-indigo-500 bg-indigo-500 text-white rounded-md px-4 py-2 m-2 select-none hover:bg-indigo-600 focus:outline-none focus:shadow-outline w-16 h-11 mt-9">
                                <i class="fa-solid fa-download"></i>
                            </Link>
                            
                            
                        </div>   
                        {/* <div className="flex flex-col">
                            <label for="order_date" className="mb-7 font-semibold"></label>
                            <Link onClick={orderDownload} className="border border-indigo-500 bg-indigo-500 text-white rounded-md px-4 py-2 m-2 transition duration-500 select-none hover:bg-indigo-600 focus:outline-none focus:shadow-outline w-16"> 
                                <i class="fa-solid fa-download"></i>
                            </Link>
                        </div> */}
                </div>
                </form>
            </div>
            <PageTitle>Orders List</PageTitle>
            <div class="w-full mb-8 overflow-hidden rounded-lg shadow-lg">
                <div class="w-full overflow-x-auto">
                    <table className="w-full whitespace-no-wrap">
                        <tr className="text-lg font-semibold tracking-wide text-left text-gray-500 capitalize border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <th className="px-4 py-3 text-left">Order Number</th>
                            <th className="px-4 py-3 text-left">Order Time</th>                             
                            <th className="px-4 py-3 text-left">Company</th>
                            <th className="px-4 py-3 text-left">Branch</th>
                            <th className="px-4 py-3 text-right">Amount</th>
                            <th className="px-4 py-3 text-center">Status</th>
                            {/* <th className="px-2 py-2 text-left">Action</th> */}
                        </tr>
                        <tbody className="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-400">
                            {orders.data.map((value, index) => (
                            <tr key={index} className="text-gray-700 dark:text-gray-400">
                                <td className="px-2 py-2 text-left">                                    
                                    <Link  href={route('orders.show', value.id )} className="text-blue-700"> 
                                       {value.order_number} 
                                    </Link>
                                    
                                </td>
                                <td className="px-2 py-2 text-left">
                                    {
                                        <DateFormat date={value.created_at} dateFormat={'dd-MMM-yyyy'}></DateFormat>
                                    }
                                    {
                                        <DateFormat date={value.created_at} dateFormat={'h:mm aa'}></DateFormat>
                                    }
                                </td>
                                <td className="px-2 py-2 text-left">
                                    {value.company?.name}
                                </td>
                                <td className="px-2 py-2 text-left">
                                    {value.shop?.title}
                                </td>
                                <td className="px-2 py-2 text-right">
                                    {value.total_amount.toLocaleString()}
                                </td>
                                <td className="px-2 py-2 text-center">
                                    <Status status={value.order_status}></Status>
                                </td>
                                {/* <td className="px-2 py-2 text-center">
                                    <Link  href={route('orders.edit',value.id )} className="bg-orange-400 hover:btnInfo text-white py-2 mr-2 px-4 shadow-md rounded"> 
                                        <i data-key={value.id} className="fas fa-pencil"></i>
                                    </Link>
                                </td> */}
                            </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                <Pagination links={orders.links} />
            </div>
        </Authenticated>
    );
}
