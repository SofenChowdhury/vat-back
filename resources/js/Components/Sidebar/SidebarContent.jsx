import { Link,usePage,useForm } from "@inertiajs/inertia-react";
import React,{useState} from "react";
function SidebarContent({auth}) {
    const { url, component } = usePage();
    const [isActive, setActive] = useState(false);
    const { data, setData} = useForm({
         urlLInk:url
    })
        
    const ToggleClass = (e) => {
        e.preventDefault();
        const value = e.target.attributes.getNamedItem("data-url").value;
        setActive(!isActive);
        setData({
            ...data,
            urlLInk:value
        })
    };   
    return (
    <div className="py-4 text-gray-500 dark:text-gray-400">

        <div className='logo w-4/5 mx-auto'>
            <Link href={route('dashboard')} 
                    className="text-2xl font-bold text-gray-800 dark:text-gray-200" > 
                <img src={`/storage/settings/d2ae3edc-df52-443c-986c-5f9881fe28bd.png`}/>
            </Link>
        </div>
            <div className="sidebar-menu">
            <ul className="mt-6">
            <li className={ data.urlLInk =='/admin/dashboard'  ? 'relative px-6 py-3 sidebar-dropdown-link-active' :  'relative px-6 py-3'}>
                <Link
                    href={route('dashboard')}
                    className="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 dark:hover:text-gray-200"
                >
                    <i className="fa fa-home"></i>
                    <span className="ml-4">Dashboard</span>
                </Link>
            </li>
            {auth?.permissions?.map((permission, index) => (
                
               <>                
                
                {permission.name == "show order" &&
                    <li  className={ data.urlLInk =='/admin/orders'  ? 'relative px-6 py-3 sidebar-dropdown-link-active' :  'relative px-6 py-3'}>
                        <Link
                            // href="/admin/orders"
                            href={route('orders.index')}
                            className="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 dark:hover:text-gray-200"
                        >
                        <i className="fas fa-shopping-cart"></i>
                            <span className="ml-4">Orders</span>
                        </Link>
                    </li>  
                }


                {permission.name == "show product" &&

                    <li className="sidebar-dropdown">
                        <a className={ data.urlLInk ==='/admin/products' || data.urlLInk==='/admin/products/create' ? 'sidebar-dropdown-link sidebar-dropdown-link-active':'sidebar-dropdown-link'}
                                data-url="/admin/products"
                                onClick={ToggleClass}
                        >
                            <i className="sidebar-menu-icon fas fa-box-open" />
                            <i className="sidebar-dropdown-right-icon fas fa-angle-right"></i>
                            <span
                            data-url="/admin/products"
                            onClick={ToggleClass}
                            >Product</span>
                            
                        </a>
                        <div  className={data.urlLInk ==='/admin/products' && isActive? "block":"sidebar-submenu" }>
                            <ul className="sidebar-submenu-ul">
                                <li className="sidebar-submenu-li"> 
                                    <Link href={route('products.index')} 
                                        className={ url ==='/admin/products' ? "sidebar-menu-link link-active inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" : "sidebar-menu-link inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"} >
                                        <i className="fas fa-box-open" />
                                        <span className="ml-4">Products</span>
                                    </Link>
                                </li>
                                <li className="sidebar-submenu-li"> 
                                    <Link href={route('products.bulk.update.form')} 
                                        className={ url ==='/admin/products' ? "sidebar-menu-link link-active inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" : "sidebar-menu-link inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"} >
                                        <i className="fas fa-file-csv" />
                                        <span className="ml-4">Bulk Update</span>
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </li>
                }
                
                {permission.name == "show customer" &&
                    <li className={ data.urlLInk =='/admin/customers'  ? 'relative px-6 py-3 sidebar-dropdown-link-active' :  'relative px-6 py-3'}>
                        <Link
                            href={route('customers.index')}
                            className="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 dark:hover:text-gray-200"
                        >
                            <i className="fa fa-user"></i> 
                            <span className="ml-4">Customers</span>
                        </Link>
                    </li>
                }

                {permission.name == "show category" && 

                    <li  className={ data.urlLInk =='/admin/categories'  ? 'relative px-6 py-3 sidebar-dropdown-link-active' :  'relative px-6 py-3'}>
                        <Link href={route('categories.index')} className="inline-flex items-center w-full text-sm font-semibold transition-colors">
                            <i className="fas fa-shopping-cart"></i>
                                <span className="ml-4">Category</span>
                        </Link>
                    </li> 
                }

                {permission.name == "show series" && 

                    <li  className={ data.urlLInk =='/admin/series'  ? 'relative px-6 py-3 sidebar-dropdown-link-active' :  'relative px-6 py-3'}>
                        <Link href={route('series.index')}
                            className="inline-flex items-center w-full text-sm font-semibold transition-colors">
                        <i className="fas fa-shopping-cart"></i>
                            <span className="ml-4">Series</span>
                        </Link>
                    </li>
                }
                {permission.name == "show company" && 
                    <li className={ data.urlLInk =='/admin/companies'  ? 'relative px-6 py-3 sidebar-dropdown-link-active' :  'relative px-6 py-3'}>
                        <Link
                            // href="/admin/orders"
                            href={route('companies.index')}
                            className="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 dark:hover:text-gray-200"
                        >
                        <i className="fa fa-store"></i>
                            <span className="ml-4">Company</span>
                        </Link>
                    </li>
                }

                {permission.name == "show branch" && 
                    <li className={ data.urlLInk =='/admin/shops'  ? 'relative px-6 py-3 sidebar-dropdown-link-active' :  'relative px-6 py-3'}>
                        <Link
                            href={route('shops.index')}
                            className="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 dark:hover:text-gray-200"
                        >
                        <i className="fa fa-store"></i>
                            <span className="ml-4">Branch</span>
                        </Link>
                    </li>
                }

                {permission.name == "show user" && 
                    <li className={ data.urlLInk =='/admin/admins'  ? 'relative px-6 py-3 sidebar-dropdown-link-active' :  'relative px-6 py-3'}>
                        <Link
                            href={route('admins.index')}
                    
                            className="inline-flex items-center w-full text-sm font-semibold transition-colors">
                                <i className="fas fa-users"></i> 
                                <span className="ml-4">Manage User</span>
                        </Link>
                    
                    </li>
                }

                {permission.name == "show role" && 
                    <li className={ data.urlLInk =='/admin/roles'  ? 'relative px-6 py-3 sidebar-dropdown-link-active' :  'relative px-6 py-3'}>
                        <Link
                            href={route('roles.index')}
                            className="inline-flex items-center w-full text-sm font-semibold transition-colors"
                        >
                        
                                <i class="fas fa-gear"></i>
                                <span className="ml-4">Manage Role</span>
                            </Link>
                    </li>
                }

                {permission.name == "show permission" && 
                    <li className={ data.urlLInk =='/admin/permissions'  ? 'relative px-6 py-3 sidebar-dropdown-link-active' :  'relative px-6 py-3'}>
                        <Link
                            href={route('permissions.index')}
                            className="inline-flex items-center w-full text-sm font-semibold transition-colors"
                        >
                            <i className="fas fa-cogs"></i>
                            <span className="ml-4">Manage Permissions</span>
                        </Link>
                    </li>
                }

                {permission.name == "show permission" && 
                    <li className={ data.urlLInk =='/admin/settings'  ? 'relative px-6 py-3 sidebar-dropdown-link-active' :  'relative px-6 py-3'}>
                        <Link
                            href={route('setting.edit')}
                            className="inline-flex items-center w-full text-sm font-semibold transition-colors"
                        >
                            <i className="fas fa-cogs"></i>
                            <span className="ml-4">Settings</span>
                        </Link>
                    </li>
                }

                {permission.name == "show payment" && 
                    <li className={ data.urlLInk =='/admin/payments'  ? 'relative px-6 py-3 sidebar-dropdown-link-active' :  'relative px-6 py-3'}>
                        <Link href={route('payments.index')}
                            className="inline-flex items-center w-full text-sm font-semibold transition-colors">
                            <i className="fas fa-money-bill"></i>
                            <span className="ml-4">Payment</span>
                        </Link>
                    </li>
                 } 
               
               </>
            ))}    
                    

            </ul>
        </div>
    </div>
    );
}

export default SidebarContent;
