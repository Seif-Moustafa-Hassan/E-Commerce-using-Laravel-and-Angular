<template>
  <div class="container my-5">
    <div class="text-center py-4">
      <h2 class="text-primary">My Previous Orders</h2>
    </div>

    <table class="table table-bordered table-striped mt-3" v-if="orders.length > 0">
      <thead class="table-dark">
        <tr>
          <th>Order ID</th>
          <th>Address</th>
          <th>Phone</th>
          <th>Total Price</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="order in orders" :key="order.id">
          <td>
            <router-link :to="`/order/${order.id}`">
              {{ order.id }}
            </router-link>
          </td>

          <td>{{ order.address }}</td>
          <td>{{ order.phone }}</td>
          <td>${{ order.total }}</td>

          <td class="d-flex gap-2">

            <!-- UPDATE BUTTON -->
            <router-link
              :to="`/order/${order.id}/update`"
              class="btn btn-warning btn-sm"
            >
              Update
            </router-link>

            <!-- DELETE BUTTON -->
            <button
              class="btn btn-danger btn-sm"
              @click="deleteOrder(order.id)"
            >
              Delete
            </button>

          </td>
        </tr>
      </tbody>
    </table>

    <p v-else class="text-warning mt-3">You have no previous orders.</p>
  </div>
</template>

<script>
import axios from "axios";
import { ref, onMounted } from "vue";

export default {
  name: "MyOrders",

  setup() {
    const orders = ref([]);

    const fetchOrders = async () => {
      try {
        const token = localStorage.getItem("token");

        const response = await axios.get(
          "http://127.0.0.1:8000/api/auth/my_orders",
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        if (response.data.success) {
          orders.value = response.data.orders;
        }
      } catch (error) {
        console.error("Error fetching orders:", error.response || error);
      }
    };

    // DELETE ORDER
    const deleteOrder = async (orderId) => {
      const confirmDelete = confirm("Are you sure you want to delete this order?");

      if (!confirmDelete) return;

      try {
        const token = localStorage.getItem("token");

        const response = await axios.delete(
          `http://127.0.0.1:8000/api/auth/delete_order/${orderId}`,
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        if (response.data.success) {
          // Remove the order from the table instantly
          orders.value = orders.value.filter(o => o.id !== orderId);

          alert("Order deleted successfully.");
        } else {
          alert(response.data.message || "Failed to delete order.");
        }

      } catch (error) {
        alert("Error deleting order.");
        console.error(error);
      }
    };

    onMounted(fetchOrders);

    return {
      orders,
      deleteOrder,
    };
  },
};
</script>

<style scoped>
table {
  width: 100%;
}
</style>
