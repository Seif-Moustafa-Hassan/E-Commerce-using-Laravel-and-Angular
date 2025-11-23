<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-3">
    <div class="container">
      <router-link class="navbar-brand" to="/">My App</router-link>

      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#mainNavbar"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNavbar">
        <ul class="navbar-nav ms-auto">
          <!-- Register link -->
          <li class="nav-item" v-if="!auth.isAuthenticated">
            <router-link class="nav-link" to="/register">Register</router-link>
          </li>

          <!-- Login link -->
          <li class="nav-item" v-if="!auth.isAuthenticated">
            <router-link class="nav-link" to="/login">Login</router-link>
          </li>

          <!-- Home link -->
          <li class="nav-item" v-if="auth.isAuthenticated">
            <router-link class="nav-link" to="/home">Home</router-link>
          </li>

          <li class="nav-item" v-if="auth.isAuthenticated">
            <router-link class="nav-link" to="/order">Order</router-link>
          </li>

          <li class="nav-item" v-if="auth.isAuthenticated">
            <router-link class="nav-link" to="/orders">My Orders</router-link>
          </li>

          <!-- Logout -->
          <li class="nav-item" v-if="auth.isAuthenticated">
            <a class="nav-link" href="#" @click.prevent="logout">Logout</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { auth } from "../store/auth";
import { useRouter } from "vue-router";

const router = useRouter();

// Use logout from auth.js
const logout = () => {
  auth.logout();
  router.push("/login");
};

// Check token on mount; if missing, log out automatically
if (!localStorage.getItem("token")) {
  logout();
}

console.log(localStorage.getItem("token"))

</script>
