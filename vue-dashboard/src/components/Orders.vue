<template>
  <div class="container my-5">
    <h2 class="mb-4">Place Order</h2>

    <!-- Order Form -->
    <form @submit.prevent="placeOrder">
      <!-- Address -->
      <div class="mb-3">
        <input 
          type="text" 
          v-model="address" 
          class="form-control" 
          placeholder="Address" 
          required 
        />
      </div>

      <!-- Phone -->
      <div class="mb-3">
        <input 
          type="text" 
          v-model="phone" 
          class="form-control" 
          placeholder="Phone" 
          required 
        />
      </div>

      <!-- Products Selection -->
      <div class="mb-3">
        <label>Select Products:</label>
        <div v-for="product in products" :key="product.id" class="mb-2">
          <input
            type="number"
            class="form-control w-25 d-inline-block"
            v-model.number="quantities[product.id]"
            :min="0"
            :max="product.stock"
          />
          <span class="ms-2">{{ product.name }} (Stock: {{ product.stock }})</span>
        </div>
      </div>

      <!-- Submit Button -->
      <button type="submit" class="btn btn-primary">Place Order</button>
    </form>

    <!-- Feedback Message -->
    <div v-if="message" class="alert mt-3" :class="messageType">
      {{ message }}
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { ref, onMounted } from "vue";

export default {
  name: "Orders",
  setup() {
    const products = ref([]);
    const address = ref("");
    const phone = ref("");
    const quantities = ref({});
    const message = ref("");
    const messageType = ref("");

    // Fetch products with token verification
    const fetchProducts = async () => {
      const token = localStorage.getItem("token");
      if (!token) {
        message.value = "You must be logged in to place an order!";
        messageType.value = "alert-danger";
        return;
      }

      try {
        const res = await axios.get("http://127.0.0.1:8000/api/auth/products", {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
          },
        });

        products.value = res.data.products;
        // Initialize quantities for each product
        products.value.forEach((p) => (quantities.value[p.id] = 0));
      } catch (err) {
        console.error("AxiosError:", err.response || err);
        message.value = err.response?.data?.message || "Failed to fetch products";
        messageType.value = "alert-danger";
      }
    };

    // Place order
    const placeOrder = async () => {
      const selectedProducts = products.value
        .filter((p) => quantities.value[p.id] > 0)
        .map((p) => ({ id: p.id, quantity: quantities.value[p.id] }));

      if (selectedProducts.length === 0) {
        message.value = "Please select at least one product";
        messageType.value = "alert-warning";
        return;
      }

      const token = localStorage.getItem("token");
      if (!token) {
        message.value = "You must be logged in to place an order!";
        messageType.value = "alert-danger";
        return;
      }

      try {
        const res = await axios.post(
          "http://127.0.0.1:8000/api/auth/orders",
          { address: address.value, phone: phone.value, products: selectedProducts },
          { headers: { Authorization: `Bearer ${token}`, Accept: "application/json" } }
        );

        message.value = `Order #${res.data.order_number} placed successfully! Total: $${res.data.total}`;
        messageType.value = "alert-success";

        // Reset form
        address.value = "";
        phone.value = "";
        products.value.forEach((p) => (quantities.value[p.id] = 0));
      } catch (err) {
        console.error("AxiosError:", err.response || err);
        message.value = err.response?.data?.message || "Failed to place order";
        messageType.value = "alert-danger";
      }
    };

    // Fetch products when component mounts
    onMounted(fetchProducts);

    return { products, address, phone, quantities, message, messageType, placeOrder };
  },
};
</script>

<style scoped>
/* Optional spacing */
input[type="number"] {
  max-width: 80px;
}
</style>
