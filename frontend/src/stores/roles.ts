import { defineStore } from 'pinia';
import { ref } from 'vue';
import { api } from "@/utils/axios";
import { useHelper } from '@/composables/helper';
import { useQuasar } from 'quasar';

export const useRolesStore = defineStore('roles', () => {
    const roles = ref([]);
    const $q = useQuasar()
    const helper = useHelper()

    async function fetch() {
        const response = await api.get('/api/admin/roles');
        roles.value = response.data;
    }

    async function create(data: any) {
        try {
            const response = await api.post('/api/admin/roles', data);
            $q.notify({
                type: 'positive',
                message: 'تم اضافة السمة'
            });

            return response.data;
        } catch (error) {
            helper.handleServerError(error);
        }
    }

    async function fetchDetails(id: number): Promise<any> {
        const response = await api.get('/api/admin/roles/' + id);
        return response.data as any;
    }

    async function update(id: number, data: any) {
        try {
            await api.put('/api/admin/roles/' + id, data);
            $q.notify({
                type: 'positive',
                message: 'تم تعديل السمة'
            });
        } catch (error) {
            helper.handleServerError(error);
        }
    }

    async function destroy(id: number) {
        await api.delete('/api/admin/roles/' + id);
        $q.notify({
            color: 'green-4',
            textColor: 'white',
            icon: 'cloud_done',
            message: 'تم حذف السمة.'
        });
    }

    return { roles, fetch, fetchDetails, create, update, destroy };
});
