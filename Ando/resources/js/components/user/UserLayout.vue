<template>
  <div class="user-layout">
    <router-view></router-view>
  </div>
</template>

<script>
export default {
  name: 'UserLayout'
}
</script>

<style scoped>
.user-layout {
  width: 100%;
  min-height: 100vh;
}
</style>
