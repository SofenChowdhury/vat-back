import React from "react";

import SidebarContent from "./SidebarContent";

export default function Sidebar({auth}) {
    return (
        <>
            <SidebarContent auth={auth}></SidebarContent>
        </>
    );
}
