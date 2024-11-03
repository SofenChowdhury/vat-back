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
    const isCreatePayment = permissions.some(permission => {
        if (permission.name === 'create payment') {
            return true;
        }
        return false;
    });

    const isEditPayment = permissions.some(permission => {
        if (permission.name === 'edit payment') {
            return true;
        }
        return false;
    });

    const isDeletePayment = permissions.some(permission => {
        if (permission.name === 'delete payment') {
            return true;
        }
        return false;
    });

    const isShowCompany = permissions.some(permission => {
        if (permission.name === 'show company') {
            return true;
        }
        return false;
    });

    const {payments, companies, shops} = usePage().props;
    const [editForm, setEditForm] = useState(null); 
    const { data, setData, post, processing, errors, reset } = useForm({
        order_number: "",
        customer_phone: "",
        company_id: "",
        shop_id: "",
        payment_date: "",
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
        post(route("orders.search"));
    };

    const orderDownload = (e) => {
        e.preventDefault();
        window.open(route('order.download'), 'noopener,noreferrer');
    };
    if (!payments) return "No Product found!";
    return (
        <Authenticated auth={props.auth} errors={props.errors}>
            <Head title="Payment List" />
            <PageTitle>Payment Search</PageTitle>
            
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
                                    <option value="approved">
                                        Approved
                                    </option>                          

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
                            <label for="order_date" className="mb-2 font-semibold">Payment Date</label>
                            
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
                    </div>
                </form>
            </div>
            <PageTitle>Payment List</PageTitle>
            <Link  href={route('payments.create')} className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 border border-blue-700 rounded w-24 mb-5 text-base"> 
                <i  className="fas fa-plus"></i> Add 
            </Link>
            <div className="mb-4 w-full rounded-lg bg-white shadow-md dark:bg-gray-800">                
                <div class="w-full overflow-x-auto">
                    <table className="w-full whitespace-no-wrap">
                        <tr className="font-semibold tracking-wide text-left text-gray-500 capitalize border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800 text-lg">
                            <th className="px-4 py-3 text-left">Payment Number</th>                             
                            <th className="px-4 py-3 text-left">Payment Date</th>                             
                            <th className="px-4 py-3 text-left">Company</th>
                            <th className="px-4 py-3 text-left">Company Branch</th>
                            <th className="px-4 py-3 text-left">Bank</th>
                            <th className="px-4 py-3 text-left">Branch Branch</th>
                            <th className="px-4 py-3 text-left">Account No.</th>
                            <th className="px-4 py-3 text-right">Amount</th>
                            <th className="px-4 py-3 text-center">Status</th>
                            <th className="px-2 py-2 text-center">Slip</th>
                            <th className="px-2 py-2 text-center">Action</th>
                        </tr>
                        <tbody className="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-400">
                            {payments.data.map((payment, index) => (
                            <tr key={index} className="text-gray-700 dark:text-gray-400">
                                <td className="px-2 py-2 text-left">
                                    <Link  href={route('payments.show',payment.id )} className="text-blue-700"> 
                                        {payment.payment_number}
                                    </Link>
                                </td>
                                <td className="px-2 py-2 text-left">
                                    {
                                        <DateFormat date={payment.created_at} dateFormat={'dd-MMM-yyyy'}></DateFormat>
                                    }
                                    {
                                        <DateFormat date={payment.created_at} dateFormat={'h:mm aa'}></DateFormat>
                                    }
                                </td>
                                <td className="px-2 py-2 text-left">
                                    {payment.company.name}
                                </td>
                                <td className="px-2 py-2 text-left">
                                    {payment.shop.title}
                                </td>
                                <td className="px-2 py-2 text-left">
                                    {payment.bank}
                                </td>
                                <td className="px-2 py-2 text-left">
                                    {payment.branch}
                                </td>
                                <td className="px-2 py-2 text-left">
                                    {payment.account_number}
                                </td>
                                <td className="px-2 py-2 text-right">
                                    {payment.amount_credit.toLocaleString()}
                                </td>
                                <td className="px-2 py-2 text-center">
                                    <Status status={payment.payment_status}></Status>
                                </td>
                                <td className="px-2 py-2 text-center">
                                    {payment.payment_slip}
                                </td>
                                <td className="px-2 py-2 text-center">
                                    <Link  href={route('payments.edit',payment.id )} className="bg-orange-400 hover:btnInfo text-white py-2 mr-2 px-4 shadow-md rounded"> 
                                        <i className="fas fa-pencil"></i>
                                    </Link>
                                    <Link  href={route('payments.show',payment.id )} className="bg-orange-400 hover:btnInfo text-white py-2 mr-2 px-4 shadow-md rounded"> 
                                        <i className="fas fa-eye"></i>
                                    </Link>
                                </td>
                            </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                <Pagination links={payments.links} />
            </div>
        </Authenticated>
    );
}
