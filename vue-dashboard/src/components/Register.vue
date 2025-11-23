<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">

        <div class="py-3 text-center">
          <h2>Register</h2>
        </div>

        <form @submit.prevent="register">
          <input
            type="text"
            class="form-control my-3"
            v-model="name"
            placeholder="Name"
          />
          <input
            type="email"
            class="form-control my-3"
            v-model="email"
            placeholder="Email"
          />
          <input
            type="password"
            class="form-control my-3"
            v-model="password"
            placeholder="Password"
          />
          <input
            type="password"
            class="form-control my-3"
            v-model="password_confirmation"
            placeholder="Confirm Password"
          />
          <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>

        <p class="text-danger mt-3" v-if="message">{{ message }}</p>

      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { useRouter } from "vue-router";
import { reactive, toRefs } from "vue";

export default {
  setup() {
    const router = useRouter();

    const data = reactive({
      name: "",
      email: "",
      password: "",
      password_confirmation: "",
      message: ""
    });

    const register = async () => {
      try {
        await axios.post("http://localhost:8000/api/auth/register", data);
        router.push("/login");
      } catch (error) {
        if (error.response?.data?.errors) {
          data.message = JSON.stringify(error.response.data.errors);
        } else {
          data.message = "Registration failed";
        }
      }
    };

    return { ...toRefs(data), register };
  }
};
</script>
