import React, { useState } from "react";
import Authenticated from "@/Layouts/Authenticated";
import { Link, Head, usePage, useForm } from "@inertiajs/inertia-react";
import Pagination from "@/Components/Common/Pagination";
import PageTitle from "@/Components/PageTitle";
import DateFormat from "@/Components/DateFormat";
import Status from "@/Components/order/Status";
import Button from "@/Components/Button";
import Input from "@/Components/Input";

export default function Index(props) {
    const [permissions, setPermissions] = useState(props.auth.permissions);
    
    const [editForm, setEditForm] = useState(null); 
    const { data, setData, post, processing, errors, reset } = useForm({
        csvfile: ""
    });

    const onHandleChange = (event) => {
        setData(
            event.target.name,
            event.target.type === "file"
                ? event.target.files[0]:event.target.value
        );
    };
    const uploadItem = (e) => {
        e.preventDefault();
        post(route('products.bulk.update'),data,{
            forceFormData: true
        });
    };

    const orderDownload = (e) => {
        e.preventDefault();
        window.open(route('order.download'), 'noopener,noreferrer');
    };
    
    return (
        <Authenticated auth={props.auth} errors={props.errors}>
            <Head title="Product Bulk Update" />
            <PageTitle>Product Bulk Update</PageTitle>
            <div className="mb-4 w-full rounded-lg bg-white p-4 shadow-md dark:bg-gray-800">
                <form onSubmit={uploadItem }>
                    <div>
                        <label class="block text-sm mb-3">
                            <h3 className="text-2xl">Upload CSV File</h3>
                            <Input
                                type="file"
                                name="csvfile"
                                id="csvfile"
                                className="w-full max-w-lg rounded border border-slate-200 px-2 py-1 border-gray-300 dark:border-gray-600 
                                dark:bg-gray-700 focus:border-purple-400 focus:outline-none 
                                focus:shadow-outline-purple dark:text-gray-300 
                                dark:focus:shadow-outline-gray form-input"
                                handleChange={onHandleChange}
                            />
                        </label>
                    </div>
                    <div>
                            <Button type="submit" className="border border-blue-300 bg-blue-500 text-white rounded-md px-4 py-2 m-2 select-none hover:bg-indigo-600 focus:outline-none focus:shadow-outline w-40 h-11 mt-9">
                                <i class="fa-solid fa-save"></i> Upload
                            </Button>
                            <Link onClick={orderDownload} className="border border-indigo-500 bg-indigo-500 text-white rounded-md px-4 py-2 m-2 select-none hover:bg-indigo-600 focus:outline-none focus:shadow-outline h-11 mt-9">
                                <i class="fa-solid fa-download"></i>
                                 Download Template
                            </Link>
                    </div>
                </form>
            </div>
            
            
        </Authenticated>
    );
}
