import { api } from "@/utils/axios";
import { defineStore } from "pinia";
import { ref } from "vue";

export const useCategoriesStore = defineStore("categories", () => {
  const categories = ref<any>({
    posts: [],
    videos: []
  })


  async function fetchCategories(type: string) {
    const response = await api.get("/api/admin/" + type + "/categories")
    categories.value[type] = response.data;
  }

  return { categories, fetchCategories };
});
