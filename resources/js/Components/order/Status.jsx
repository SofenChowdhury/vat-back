import React from 'react';

export default function Status({ status }) {
    return (
        <span className='ml-1'>
            {status =="pending" &&
                <span className="px-2 py-1 font-semibold leading-tight text-gray-700 bg-yellow-300 rounded-sm dark:text-gray-100 dark:bg-gray-700 capitalize">
                    {status}
                </span>
            }
            {status =="Pending" &&
                <span className="px-2 py-1 font-semibold leading-tight text-gray-700 bg-yellow-300 rounded-sm dark:text-gray-100 dark:bg-gray-700 capitalize">
                    {status}
                </span>
            }
            {status =="confirmation" &&
                <span className="px-2 py-1 font-semibold leading-tight text-white bg-blue-500  rounded-sm dark:text-gray-100 dark:bg-gray-700 capitalize">
                    {status}
                </span>
            }
            {status =="sap_confirmation" &&
                <span className="px-2 py-1 font-semibold leading-tight text-gray-700 bg-green-300 rounded-sm dark:text-gray-100 dark:bg-gray-700 capitalize">
                    SAP Confirmation
                </span>
            }
            {status =="hold" &&
                <span className="px-2 py-1 font-semibold leading-tight text-white bg-red-500 rounded-sm dark:text-gray-100 dark:bg-gray-700 capitalize">
                    {status}
                </span>
            }
            {status =="in_delivery" &&
                <span className="px-2 py-1 font-semibold leading-tight text-white bg-purple-500 rounded-sm dark:text-gray-100 dark:bg-gray-700 capitalize">
                    In Delivery
                </span>
            }
            {status =="received" &&
                <span className="px-2 py-1 font-semibold leading-tight text-white bg-purple-800 rounded-sm dark:text-gray-100 dark:bg-gray-700 capitalize">
                    {status}
                </span>
            }
            {status =="delivered" &&
                <span className="px-2 py-1 font-semibold leading-tight text-white bg-green-600 rounded-sm dark:text-gray-100 dark:bg-gray-700 capitalize">
                    {status}
                </span>
            }
            {status =="Unreached" &&
                <span className="px-2 py-1 font-semibold leading-tight text-white bg-orange-500 rounded-sm dark:text-gray-100 dark:bg-gray-700 capitalize">
                    {status}
                </span>
            }
            {status =="declined" &&
                <span className="px-2 py-1 font-semibold leading-tight text-white bg-orange-300 rounded-sm dark:text-gray-100 dark:bg-gray-700 capitalize">
                    {status}
                </span>
            }
            {status =="returned" &&
                <span className="px-2 py-1 font-semibold leading-tight text-white bg-orange-300 rounded-sm dark:text-gray-100 dark:bg-gray-700 capitalize">
                    {status}
                </span>
            }
            
            {status =="0" &&
                <span className="px-2 py-1 font-semibold leading-tight text-white bg-orange-300 rounded-sm dark:text-gray-100 dark:bg-gray-700 capitalize">
                    Pending
                </span>
            }
            {status =="1" &&
                <span className="px-2 py-1 font-semibold leading-tight text-white bg-green-600 rounded-sm dark:text-gray-100 dark:bg-gray-700 capitalize">
                    Approved
                </span>
            }  
            {status =="2" &&
                <span className="px-2 py-1 font-semibold leading-tight text-white bg-orange-600 rounded-sm dark:text-gray-100 dark:bg-gray-700 capitalize">
                    Denied
                </span>
            }       
        </span>
        
        
    );
}
