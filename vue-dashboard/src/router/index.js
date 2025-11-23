import { createRouter, createWebHistory } from "vue-router";
import Register from "../components/Register.vue";
import Login from "../components/Login.vue";
import Home from "../components/Home.vue";
import ProductDetails from "../components/ProductDetails.vue";
import Orders from "../components/Orders.vue";
import NotFound from "../components/NotFound.vue";
import MyOrders from "../components/MyOrders.vue";
import OrderDetails from "../components/OrderDetails.vue";
import UpdateOrder from "../components/UpdateOrder.vue";

const routes = [
    { path: "/register", component: Register, meta: { guest: true } },
    { path: "/login", component: Login, meta: { guest: true } },
    { path: "/home", component: Home, meta: { auth: true } },
    { path: "/product/:id", component: ProductDetails, meta: { auth: true } },
    { path: "/order", component: Orders, meta: { auth: true } },
    { path: "/orders", component: MyOrders, meta: { auth: true } },
    { path: "/order/:id", component: OrderDetails, meta: { auth: true } },
    { path: "/order/:id/update", component: UpdateOrder, meta: { auth: true } },

    // Catch-all route for 404 page
    { path: "/:pathMatch(.*)*", name: "NotFound", component: NotFound }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Route guards
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem("token");

    if (to.meta.auth && !token) {
        // If page requires auth and user is not logged in
        next("/login");
    } else if (to.meta.guest && token) {
        // If page is guest-only and user is logged in
        next("/home");
    } else {
        next();
    }
});

export default router;
