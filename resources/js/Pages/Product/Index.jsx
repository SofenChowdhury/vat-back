import React, { useState } from "react";
import Authenticated from "@/Layouts/Authenticated";
import { Link, Head, usePage } from "@inertiajs/inertia-react";
import Pagination from "@/Components/Common/Pagination";
import PageTitle from "@/Components/PageTitle";

export default function Index(props) {
    const { products } = usePage().props;
    console.log(products);
    if (!products) return "No Product found!";
    return (
        <Authenticated auth={props.auth} errors={props.errors}>
            <Head title="Product" />
            
                 
            <PageTitle>Product List</PageTitle>   
            
            <div className="grid-rows-1">
                <Link  href={route('products.create')} className="bg-purple-600 border border-transparent active:bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 mr-2 border-purple-900 rounded w-24 mb-5 text-base"> 
                    <i  className="fas fa-plus"></i> Add 
                </Link>    
                <Link  href={route('products.download')} className="bg-blue-600 border border-transparent active:bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 border-purple-900 mr-2 rounded w-24 mb-5 text-base"> 
                    <i  className="fas fa-download"></i> Download 
                </Link>
            </div>
            <div className="w-full overflow-hidden rounded-lg by  mb-8 drop-shadow-lg mt-5">
                <div className="w-full overflow-x-auto">
                    <table className="w-full whitespace-no-wrap">
                        <thead className="text-lg font-semibold tracking-wide text-left text-gray-500 capitalize border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <tr>
                                <th className="px-4 py-3">#</th>
                                <th className="px-4 py-3">Tile</th>
                                <th className="px-4 py-3">SKU</th>
                                {/* <th className="px-4 py-3">Model</th> */}
                                <th className="px-4 py-3">Thumbnail</th>    
                                <th className="px-4 py-3">Variants</th>                             
                                <th className="px-4 py-3">Stock</th>
                                <th className="px-4 py-3">Price</th>                                 
                                <th className="px-4 py-3">Status</th> 
                                <th className="px-4 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-400">
                            {products.data.map((value, index) => (
                                <tr key={index}>
                                    <td className="px-4 py-3">
                                        {value.id}
                                    </td>
                                    <td className="px-4 py-3">
                                         {value.title}
                                    </td>
                                    <td className="px-4 py-3">
                                        {value.sku}
                                    </td>
                                    {/* <td className="px-4 py-3">
                                        {value.model}
                                    </td> */}
                                    <td className="px-4 py-3">
                                        <img src={`/storage/thumbnails/${value.photo}`} width={100}/>
                                    </td>
                                    <td className="px-4 py-3">{value.product_type}</td>
                                    <td className="px-4 py-3"> {value.stock}</td>
                                    <td className="px-4 py-3"> {value.regular_price.toLocaleString()}</td>
                                    <td className="px-4 py-3">
                                        <span className={value.status ? 'bg-green-100 text-green-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded dark:bg-green-200 dark:text-green-900'
                                         :'bg-red-100 text-red-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded dark:bg-red-200 dark:text-red-900'}>
                                        {value.status ? "active"  :'Unactive'}
                                    
                                       </span>
                                    </td>
                                    
                                    <td className="px-4 py-3  text-right">

                                       <Link href={route('products.edit', value.id)} className="btnInfo hover:btnInfo text-white py-2 mr-2 px-4 shadow-md rounded"> 
                                            <i  className="fas fa-pencil"></i>
                                        </Link>

                                        <Link href={route('attribute.add', value.id)} title={value.status? "Inactive"  : "Active"}
                                            className={value.status ?'bg-green-400 hover:bg-green-500 text-white mr-2 py-2 px-4 rounded' :'bg-red-400 hover:bg-red-500 text-white mr-2 py-2 px-4 rounded'}> 
                                            <i className= {value.status ? "fas fa-long-arrow-up" : 'fas fa-long-arrow-down'}></i>
                                        </Link>

                                        <Link href={route('gallery.add' , value.id)} className="bg-green-400 hover:bg-green-500 text-white py-2 mr-2 px-4 rounded"> 
                                            <i className="fas fa-plus"></i> Gallery
                                        </Link>
                                        <Link href={route('variant.add', value.id)} className="bg-hyandai-400 hover:bg-hyandai-500 text-white py-2 px-4 rounded"> 
                                            <i className="fas fa-plus"></i> Add Variant 
                                         </Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                <Pagination links={products.links} />
            </div>
        </Authenticated>
    );
}
