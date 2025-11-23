<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">

        <div class="py-3 text-center">
          <h2>Login</h2>
        </div>

        <form @submit.prevent="login">
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
          <button type="submit" class="btn btn-primary w-100">Login</button>
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
import { auth } from "../store/auth";

export default {
  setup() {
    const router = useRouter();

    const data = reactive({
      email: "",
      password: "",
      message: ""
    });

    const login = async () => {
      try {
        const res = await axios.post(
          "http://localhost:8000/api/auth/login",
          {
            email: data.email,
            password: data.password
          }
        );
        // Save token and update navbar immediately
        auth.login(res.data.access_token);

        router.push("/home");
      } catch (error) {
        if (error.response && error.response.data.error) {
          data.message = error.response.data.error;
        } else {
          data.message = "Login failed";
        }
      }
    };

    return { ...toRefs(data), login };
  }
};
</script>
