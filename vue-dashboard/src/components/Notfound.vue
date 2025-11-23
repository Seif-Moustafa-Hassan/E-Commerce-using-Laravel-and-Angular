<template>
  <div class="container text-center mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <h1 class="display-1 text-danger">404</h1>
        <h3 class="mb-4">Page Not Found</h3>
        <p>The page you are looking for does not exist.</p>
        <router-link to="/" class="btn btn-primary mt-3">Go to Home</router-link>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "NotFound"
};
</script>

<style scoped>
h1 {
  font-weight: bold;
}
</style>
