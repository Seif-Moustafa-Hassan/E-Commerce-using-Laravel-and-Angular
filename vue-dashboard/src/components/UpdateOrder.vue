<template>
  <div class="container mt-5">
    <h2 class="text-center mb-4 text-primary">Update Order</h2>

    <!-- Loading -->
    <div v-if="loading" class="text-center text-secondary">
      Loading order details...
    </div>

    <!-- Form -->
    <form v-if="!loading" @submit.prevent="updateOrder" class="card p-4 shadow-sm">

      <!-- Success -->
      <div v-if="successMessage" class="alert alert-success">
        {{ successMessage }}
      </div>

      <!-- Error -->
      <div v-if="errorMessage" class="alert alert-danger">
        {{ errorMessage }}
      </div>

      <div class="mb-3">
        <label class="form-label">Address</label>
        <input 
          type="text"
          class="form-control"
          v-model="address"
          required
        >
      </div>

      <div class="mb-3">
        <label class="form-label">Phone</label>
        <input 
          type="text"
          class="form-control"
          v-model="phone"
          required
        >
      </div>

      <button type="submit" class="btn btn-primary w-100">
        Update Order
      </button>

    </form>
  </div>
</template>

<script>
import axios from "axios";
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";

export default {
  name: "UpdateOrder",

  setup() {
    const route = useRoute();
    const router = useRouter();

    const orderId = route.params.id;

    const loading = ref(true);
    const address = ref("");
    const phone = ref("");
    const successMessage = ref("");
    const errorMessage = ref("");

    // Fetch order details to prefill the form
    const fetchOrder = async () => {
      try {
        const token = localStorage.getItem("token");

        const response = await axios.get(
          `http://127.0.0.1:8000/api/auth/my_orders`,
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        if (response.data.success) {
          const found = response.data.orders.find(o => o.id == orderId);

          if (!found) {
            errorMessage.value = "Order not found.";
            return;
          }

          address.value = found.address;
          phone.value = found.phone;
        }
      } catch (error) {
        errorMessage.value = "Failed to load order.";
      } finally {
        loading.value = false;
      }
    };

    const updateOrder = async () => {
      successMessage.value = "";
      errorMessage.value = "";

      try {
        const token = localStorage.getItem("token");

        const response = await axios.patch(
          `http://127.0.0.1:8000/api/auth/update_order/${orderId}`,
          {
            address: address.value,
            phone: phone.value,
          },
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        if (response.data.success) {
          successMessage.value = "Order updated successfully!";

          setTimeout(() => {
            router.push("/orders");
          }, 1200);
        } else {
          errorMessage.value = response.data.message;
        }
      } catch (error) {
        errorMessage.value = "Failed to update order.";
      }
    };

    onMounted(() => fetchOrder());

    return {
      loading,
      address,
      phone,
      successMessage,
      errorMessage,
      updateOrder
    };
  },
};
</script>

<style scoped>
.card {
  max-width: 450px;
  margin: 0 auto;
}
</style>
