import React, { useEffect } from 'react';
import {Head, Link, useForm,  usePage} from '@inertiajs/inertia-react';
import Input from '@/Components/Input';
import Label from '@/Components/Label';
import ValidationErrors from '@/Components/ValidationErrors';
import Button from '@/Components/Button';
import Authenticated from '@/Layouts/Authenticated';
import PageTitle from '@/Components/PageTitle';
import Checkbox from '@/Components/Checkbox';

export default function Create(props) {
    const {role, permissions} = usePage().props;
    const { data, setData, post, put, processing, errors, reset } = useForm({
        name:role?role.name: "",
        permissions: [],
    });

    const onHandleChange = (event) => {
        setData(
            event.target.name,
            event.target.type === "checkbox"
                ? event.target.checked
                : event.target.value
        );
    };

    const handleChecked = (e) => {
        let id = e.target.value;
            if (e.target.checked) {
                setData("permissions", [...data.permissions, id]);
            } else {
                setData(
                    "permissions",
                    data.permissions.filter((item) => {
                        return item !== id;
                    })
                );
            }
    };

    const submit = (e) => {
        e.preventDefault();
        put(route('roles.update', role?.id));
    };

    let back = function()
    {
        window.history.back();
    }
    return (
        <Authenticated auth={props.auth} errors={props.errors}>
            <Head title="Manage Role" />
            <PageTitle>Manage Role</PageTitle>
            
            
            <div className="grid-rows-1">
                <Link  onClick={back} className="bg-yellow-300 hover:bg-yellow-400 text-black font-bold py-2 px-4 border border-yellow-700 rounded w-24 mb-5 text-base float-left"> 
                    <i className="fas fa-chevron-left"></i> Back
                </Link>
            </div>

            <div className="mb-8 p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <ValidationErrors errors={errors} />
                <form onSubmit={submit}>
                    <label class="block text-sm mb-3">
                        <span class="text-gray-700 dark:text-gray-400">
                            Role Name
                        </span>
                        <Input
                            type="text"
                            name="name"
                            value={data.name}
                            className="block w-full mt-1 text-sm border rounded border-gray-300 dark:border-gray-600 
                            dark:bg-gray-700 focus:border-purple-400 focus:outline-none 
                            focus:shadow-outline-purple dark:text-gray-300 
                            dark:focus:shadow-outline-gray form-input"
                            autoComplete="name"
                            isFocused={true}
                            handleChange={onHandleChange}
                            required
                        />
                    </label>
                    <label class="block text-sm mb-3">
                        <span class="text-gray-700 dark:text-gray-400">
                            Give Permissions
                        </span>
                        <p>
                            {permissions && permissions.map((permission, index) => ( 
                                
                            <label class="flex items-center dark:text-gray-400 ">
                                <Checkbox name="permissions[]" id={`permission${permission.id}`} value={permission.id} handleChange={handleChecked} />
                                    
                                    <span class="m-1 capitalize">{permission.name}</span>
                              </label>
                            ))}
                        </p>
                       
                    </label>
                    <div className="flex items-center mt-4">
                        <Button className="flex items-center justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple" >
                            <i className='fas fa-save'></i>
                            <span className='m-1'>Update</span>
                        </Button>   
                                        
                    </div>
                </form>
            </div>         

        </Authenticated>
    );
}
