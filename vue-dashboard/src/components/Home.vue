<template>
  <div class="container my-5">
    <h2 class="mb-4">Home Page</h2>
    <p>Welcome {{ userName }}! You are logged in.</p>

    <h3 class="mt-5">Products List</h3>

    <table class="table table-bordered table-striped mt-3" v-if="products.length > 0">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Description</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products" :key="product.id">
          <td>{{ product.id }}</td>
          <td>
            <router-link :to="`/product/${product.id}`">{{ product.name }}</router-link>
          </td>
          <td>{{ product.description }}</td>
          <td>{{ product.price }}</td>
          <td>{{ product.stock }}</td>
          <td>
            <span v-if="product.out_of_stock" class="text-danger">Out of Stock</span>
            <span v-else class="text-success">In Stock</span>
          </td>
        </tr>
      </tbody>
    </table>

    <p v-else class="text-warning mt-3">No products found.</p>
  </div>
</template>

<script>
import axios from "axios";
import { ref, onMounted } from "vue";

export default {
  name: "Home",
  setup() {
    const products = ref([]);
    const userName = ref(""); // For displaying user's name

    const fetchUser = async () => {
      try {
        const token = localStorage.getItem("token");
        const response = await axios.get(
          "http://127.0.0.1:8000/api/auth/me",
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );

        if (response.data.success) {
          userName.value = response.data.user.name; // Set user's name
        }
      } catch (error) {
        console.error("Error fetching user:", error.response || error);
      }
    };

    const fetchProducts = async () => {
      try {
        const token = localStorage.getItem("token");
        const response = await axios.get(
          "http://127.0.0.1:8000/api/auth/products",
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );

        products.value = response.data.products || [];
      } catch (error) {
        console.error("Error fetching products:", error.response || error);
      }
    };

    onMounted(() => {
      fetchUser();      // fetch user's name
      fetchProducts();  // fetch products list
    });

    return { products, userName };
  },
};
</script>

<style scoped>
table {
  width: 100%;
}
</style>
