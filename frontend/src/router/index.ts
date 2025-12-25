import { createRouter, createWebHistory } from 'vue-router';
import WilliamsIndicators from '@/views/WilliamsIndicators.vue';

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'Home',
      component: WilliamsIndicators
    }
  ]
});

