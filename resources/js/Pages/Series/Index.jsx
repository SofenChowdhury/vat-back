import React, {useState} from "react";
import { Inertia } from '@inertiajs/inertia'
import Authenticated from '@/Layouts/Authenticated';
import { Link, Head, usePage, useForm } from '@inertiajs/inertia-react';
import PageTitle from "@/Components/PageTitle";
import Label from '@/Components/Label';
import Input from '@/Components/Input';
import Button from '@/Components/Button';

export default function Index(props) {
    const [permissions, setPermissions] = useState(props.auth.permissions);
    const isCreate = permissions.some(permission => {
        if (permission.name === 'create series') {
            return true;
        }
        return false;
    });
    // 👇️ check if array contains object
    const isEdit = permissions.some(permission => {
        if (permission.name === 'edit series') {
            return true;
        }
        return false;
    });
    const {series} = usePage().props;
    const [editForm ,setEditForm] = useState(null); 
    const [showForm ,setShowForm] = useState(null); 

    const { data, setData, post, processing, errors, reset } = useForm({
        name: ''
    });

const onHandleChange = (event) => {
    setData(event.target.name, event.target.type === 'checkbox' ? event.target.checked : event.target.value);
};

const storeItem = (e) => {
    e.preventDefault();
    setShowForm(false);
    post(route('series.store'), {
        forceFormData: true        
    });
};

const addForm = (e)=>{
    e.preventDefault();
    setShowForm(true);
    setEditForm(false);
    setData({
        ...data, 
        title:"",
        uuid:"",
    });
 }

 const editItem = (e) =>{
    e.preventDefault();
    setEditForm(true);
    setShowForm(true);
    const uuid =  e.target.attributes.getNamedItem("data-key").value;

    if(uuid !==""){
        axios.get("/api/series-edit/" + uuid)
        .then(res =>{
            // console.log(res.name);
            setData({
                ...data, 
                uuid:res.data.uuid,
                title:res.data.title,
                
            });
        })
        .catch(error => {
            console.log(error);
        });
    }
}
const updateItem = (e)=>{
    e.preventDefault();
    setShowForm(false);
    Inertia.put(route('series.update', data.uuid),data);
    if(toaster !== null){
        Helper.alertMessage('success', toaster.message);
    }
    e.target.reset();
}
    if (!series) return "No Series found!";

    return (
        <>
            <Authenticated auth={props.auth} errors={props.errors}> 
                <Head title="Manage Series" />
                <PageTitle>Manage Series</PageTitle>
                {isCreate &&
                     <Link onClick={addForm} className="bg-purple-600 border border-transparent active:bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 border-purple-900 rounded w-24 mb-5 text-base float-right"> 
                        <i  className="fas fa-plus"></i> Add 
                    </Link>
                } 

                {
                  showForm &&
                    <form onSubmit={editForm ? updateItem : storeItem }>
                        <div className="flex flex-row gap-3 mb-5">
                            <div className="basis-2/3 p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
                                <div className="">
                                    <Label forInput="title" className="" value="Name" />

                                    <Input
                                        type="text"
                                        name="title"
                                        value={data.title}
                                        className="block w-full mt-1 text-sm border rounded border-gray-300 dark:border-gray-600 
                                        dark:bg-gray-700 focus:border-purple-400 focus:outline-none 
                                        focus:shadow-outline-purple dark:text-gray-300 
                                        dark:focus:shadow-outline-gray form-input"
                                        autoComplete="title"
                                        isFocused={true}
                                        handleChange={onHandleChange}
                                        required
                                    />
                                </div>

                                <div className="flex items-center mt-4">
                                   <Button className="bg-green-400 hover:bg-green-500 text-white py-2 mr-2 px-4 rounded" >
                                        { editForm ? "Update":"Save"}
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </form>

                 
                }
               
               <PageTitle>Manage Series</PageTitle>
                <div className="mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
                    <div className="w-full overflow-hidden rounded-lg shadow-xs mb-8">
                        
                        <div className="w-full overflow-x-auto">
                            <table className="w-full whitespace-no-wrap">
                                <thead className="text-lg font-semibold tracking-wide text-left text-gray-500 capitalize border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                    <tr>
                                        <th className="px-4 py-3">Id</th>
                                        <th className="px-4 py-3">Series Name</th>
                                        <th className="px-4 py-3 text-right">Action</th>
                                        
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-400">
                                    {series && series.map((value, index) => (
                                        <tr key={index}>
                                            <td className="px-4 py-3">
                                                {value.id}
                                            </td>
                                            <td className="px-4 py-3">
                                                {value.title}
                                            </td>

                                            <td className="px-4 py-3  text-right">
                                                <Link  onClick={editItem} data-key={value.uuid}   className="btnInfo hover:btnInfo text-white py-2 mr-2 px-4 shadow-md rounded"> 
                                                    <i  onClick={editItem} data-key={value.uuid} className="fas fa-pencil"></i>
                                                </Link>
                                            </td>


                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
            </Authenticated>
        </>
    );
}
