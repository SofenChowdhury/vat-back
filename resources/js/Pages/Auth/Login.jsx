import React, { useEffect } from "react";
import Button from "@/Components/Button";
import Checkbox from "@/Components/Checkbox";
import Input from "@/Components/Input";
import Label from "@/Components/Label";
import ValidationErrors from "@/Components/ValidationErrors";
import { Head, Link, useForm, usePage } from "@inertiajs/inertia-react";
import Guest from "@/Layouts/Guest";
import PageTitle from "@/Components/PageTitle";

export default function Login(props,{ status, canResetPassword }) {
    const { errors } = usePage().props
    const { data, setData, post, processing, reset} = useForm({
        email: "",
        password: "",
        remember: "",
    });

    useEffect(() => {
     
        return () => {
            reset("password");
        };
    }, []);



    const onHandleChange = (event) => {
        setData(
            event.target.name,
            event.target.type === "checkbox"
                ? event.target.checked
                : event.target.value
        );
    };

    const submit = (e) => {
        e.preventDefault();

        post(route("login"));
    };


    console.log(props);

    return (
        <>
            <Guest>
                <Head title="Log in" />
                {status && (
                    <div className="mb-4 font-medium text-sm text-green-600">
                        {status}
                    </div>
                )}

                <ValidationErrors errors={errors} />
                
                <PageTitle> Log in</PageTitle>
                <form onSubmit={submit}>
                    <div className="mt-4">
                        <Label forInput="email" value="Email" />

                        <Input
                            type="text"
                            name="email"
                            value={data.email}
                            className="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input border"
                            autoComplete="username"
                            isFocused={true}
                            handleChange={onHandleChange}
                        />
                        
                    </div>

                    <div className="mt-4">
                        <Label forInput="password" value="Password" />
                        
                        <Input
                            type="password"
                            name="password"
                            value={data.password}
                            className="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input border"
                            autoComplete="current-password"
                            handleChange={onHandleChange}
                        />
                    </div>

                    <div className="flex justify-between items-center mt-4">
                        <label className="flex items-center">
                            <Checkbox
                                name="remember"
                                value={data.remember}
                                handleChange={onHandleChange}
                                className="focus:bg-purple-400 appearance-none checked:bg-purple-700"
                            />

                            <span className="ml-2 text-sm text-purple-600">
                                Remember me
                            </span>
                        </label>
                        
                            <Link
                                href={route("password.request")}
                                className="block underline text-sm text-purple-600 hover:text-gray-900"
                            >
                                Forgot your password?
                            </Link>
                        
                    </div>

                    <div className="mt-4">
                        <Button
                            className="flex items-center justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple"
                            processing={processing}
                        >
                            
                            <span className="m-1">Log In</span> 
                            <i class="fa fa-right-to-bracket"></i>
                        </Button>
                    </div>
                </form>
            </Guest>
        </>
    );
}
