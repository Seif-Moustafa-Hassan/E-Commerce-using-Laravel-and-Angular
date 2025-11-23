<template>
  <div class="container my-5 d-flex justify-content-center">
    <div v-if="product" class="card shadow-lg" style="width: 28rem;">
      <div class="card-header bg-primary text-white text-center">
        <h4>Product Details</h4>
      </div>
      <div class="card-body">
        <h5 class="card-title">{{ product.name }}</h5>
        <p class="card-text"><strong>ID:</strong> {{ product.id }}</p>
        <p class="card-text"><strong>Description:</strong> {{ product.description }}</p>
        <p class="card-text"><strong>Price:</strong> ${{ product.price }}</p>
        <p class="card-text"><strong>Stock:</strong> {{ product.stock }}</p>
        <p class="card-text">
          <strong>Status:</strong>
          <span v-if="product.out_of_stock" class="text-danger">Out of Stock</span>
          <span v-else class="text-success">In Stock</span>
        </p>
        <router-link to="/home" class="btn btn-primary w-100 mt-3">Back to Products</router-link>
      </div>
    </div>

    <div v-else class="alert alert-warning text-center" style="width: 28rem;">
      Loading product...
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";

export default {
  setup() {
    const route = useRoute();
    const product = ref(null);

    const fetchProduct = async () => {
      try {
        const token = localStorage.getItem("token");
        const response = await axios.get(
          `http://127.0.0.1:8000/api/auth/product/${route.params.id}`,
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );
        product.value = response.data.product;
      } catch (error) {
        console.error("Error fetching product:", error.response || error);
      }
    };

    onMounted(fetchProduct);

    return { product };
  },
};
</script>

<style scoped>
.card {
  border-radius: 12px;
}
.card-header {
  font-size: 1.3rem;
}
</style>
