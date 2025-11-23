<template>
  <div class="container my-5">

    <div class="text-center py-4">
      <h2 class="text-primary">Order Details</h2>
    </div>

    <div v-if="loading" class="text-center">
      <p>Loading order details...</p>
    </div>

    <div v-else-if="error" class="text-danger text-center">
      <p>{{ error }}</p>
    </div>

    <div v-else>

      <!-- Order Summary -->
      <div class="card mb-4 p-4 shadow-sm">
        <h4>Order #{{ order.order_id }}</h4>
        <p><strong>Address:</strong> {{ order.address }}</p>
        <p><strong>Phone:</strong> {{ order.phone }}</p>
        <p><strong>Total:</strong> ${{ order.total_price }}</p>
      </div>

      <!-- Order Items -->
      <table class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr>
            <th>Product Name</th>
            <th>Price</th>
            <th>Quantity</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(item, index) in order.items" :key="index">
            <td>{{ item.product_name }}</td>
            <td>${{ item.product_price }}</td>
            <td>{{ item.quantity }}</td>
          </tr>
        </tbody>
      </table>

    </div>
  </div>
</template>

<script>
import axios from "axios";
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";

export default {
  name: "OrderDetails",

  setup() {
    const route = useRoute();
    const order = ref(null);
    const loading = ref(true);
    const error = ref(null);

    const fetchOrderDetails = async () => {
      try {
        const token = localStorage.getItem("token");
        const id = route.params.id;

        const response = await axios.get(
          `http://127.0.0.1:8000/api/auth/order/${id}`,
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        if (response.data.success) {
          order.value = response.data.order;
        } else {
          error.value = response.data.message;
        }
      } catch (err) {
        error.value = "Failed to load order details.";
      } finally {
        loading.value = false;
      }
    };

    onMounted(fetchOrderDetails);

    return { order, loading, error };
  },
};
</script>

<style scoped>
table {
  width: 100%;
}
</style>
