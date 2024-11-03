import React, {useState} from "react";
import Authenticated from "@/Layouts/Authenticated";
import { Head, Link, usePage} from "@inertiajs/inertia-react";
import PageTitle from "@/Components/PageTitle";
import InfoCard from "@/Components/Cards/InfoCard";
import DateFormat from "@/Components/DateFormat";
import Status from "@/Components/order/Status";
export default function Dashboard(props) {
    const [permissions, setPermissions] = useState(props.auth.permissions);
    const isShowCompany = permissions.some(permission => {
        if (permission.name === 'show company') {
            return true;
        }
        return false;
    });
    // 👇️ check if array contains object
    const isShowCustomer = permissions.some(permission => {
        if (permission.name === 'show customer') {
            return true;
        }
        return false;
    });

    const isShowProducts = permissions.some(permission => {
        if (permission.name === 'show product') {
            return true;
        }
        return false;
    });
    const {orders, companies, shops, customers, products} = usePage().props;
    console.log(orders.data)
    return (
        <Authenticated auth={props.auth} errors={props.errors}>  
            <Head title="Dashboard" />
            <PageTitle>Dashboard</PageTitle>
            <div className="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-6">
                <InfoCard title="Total Orders" value={orders.total}>
                    <i className="fas fa-bag-shopping text-5xl text-red-700 text-cool-purple-600"></i>
                </InfoCard>
                {isShowCompany && 
                <InfoCard title="Total Company" value={companies}>
                    <i class="fas fa-building text-5xl text-blue-400 text-cool-purple-600"></i>
                </InfoCard>
                }
                <InfoCard title="Total Branch" value={shops.total}>
                    <i className="fas fa-store text-5xl text-green-400 text-cool-purple-600"></i>
                </InfoCard>
                {isShowCustomer && 
                <InfoCard title="Total Customers" value={customers.total}>
                    <i className="fas fa-users text-5xl text-orange-400 text-cool-purple-600"></i>
                </InfoCard>
                }
                {isShowProducts && 
                <InfoCard title="Total Product" value={products.total}>
                    <i className="fas fa-cart-flatbed text-5xl text-indigo-700 text-cool-purple-600"></i>
                </InfoCard>
                }
            </div>

            <h2 className="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Latest Orders
            </h2>
                        
            <div className="w-full by overflow-hidden rounded-lg shadow-lg">               
                <div className="w-full overflow-x-auto">
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
                        <tbody className="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        {orders.data && orders.data.map((value, index) => (
                            <tr className="text-gray-700 dark:text-gray-400">
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
                                {/* <td className="px-2 py-2 text-left">
                                    <Link href={route('orders.edit',value.id )} className="bg-orange-400 hover:btnInfo text-white py-2 mr-2 px-4 shadow-md rounded"> 
                                        <i data-key={value.id} className="fas fa-pencil"></i>
                                    </Link>
                                </td> */}
                            </tr>
                        ))}
                        </tbody>
                    </table>
                </div>
            </div>
            {/* <h2 className="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
                    Customers
            </h2>
            <div className="grid gap-6 mb-8 by md:grid-cols-2">
                <div className="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                    <h4 className="mb-4 font-semibold text-gray-800 dark:text-gray-300">
                        Revenue
                    </h4>
                    
                </div>
            </div>

            <div>
                <h2 className="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
                    Charts
                </h2>
                <div className="grid gap-6 mb-8 by md:grid-cols-2">
                    <div className="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                        <h4 className="mb-4 font-semibold text-gray-800 dark:text-gray-300">
                            Revenue
                        </h4>
                       <canvas id="pie" />
                        <div className="flex justify-center mt-4 space-x-3 text-gray-600 dark:text-gray-400">
                            Chart legend
                            <div className="flex items-center">
                            <span className="inline-block w-3 h-3 mr-1 bg-blue-500 rounded-full" />
                            <span>Shirts</span>
                            </div>
                            <div className="flex items-center">
                            <span className="inline-block w-3 h-3 mr-1 bg-teal-600 rounded-full" />
                            <span>Shoes</span>
                            </div>
                            <div className="flex items-center">
                            <span className="inline-block w-3 h-3 mr-1 bg-purple-600 rounded-full" />
                            <span>Bags</span>
                            </div>
                        </div>
                    </div>
                    <div className="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                        <h4 className="mb-4 font-semibold text-gray-800 dark:text-gray-300">
                            Traffic
                        </h4>
                       <canvas id="line" />
                        <div className="flex justify-center mt-4 space-x-3 text-gray-600 dark:text-gray-400">
                            
                            <div className="flex items-center">
                            <span className="inline-block w-3 h-3 mr-1 bg-teal-600 rounded-full" />
                            <span>Organic</span>
                            </div>
                            <div className="flex items-center">
                            <span className="inline-block w-3 h-3 mr-1 bg-purple-600 rounded-full" />
                            <span>Paid</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div> */}

        </Authenticated>
    );
}
