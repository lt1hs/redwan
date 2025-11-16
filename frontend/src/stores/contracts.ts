import { defineStore } from 'pinia';
import { api } from '@/boot/axios'; // Import the configured API instance

interface ContractForm {
  husband_name: string;
  husband_nationality: string;
  husband_id_number: string;
  husband_birth_date: string;
  husband_address: string;
  husband_phone: string;
  husband_passport_number: string;

  wife_name: string;
  wife_nationality: string;
  wife_id_number: string;
  wife_birth_date: string;
  wife_address: string;
  wife_phone: string;
  wife_passport_number: string;

  contract_number: string;
  contract_date: string;
  contract_type: string;
  contract_place: string;
  present_dowry: string | number;
  deferred_dowry: string | number;
  husband_conditions: string; // Keep for now, will be replaced by specific fields
  husband_conditions_arabic: string;
  husband_conditions_persian: string;
  wife_conditions: string; // Keep for now, will be replaced by specific fields
  wife_conditions_arabic: string;
  wife_conditions_persian: string;
  first_witness: string;
  second_witness: string;
  officiant_name: string;
  notes: string;
}

export const useContractsStore = defineStore('contracts', {
  state: () => ({
    contracts: [] as ContractForm[],
    contract: null as ContractForm | null,
    loading: false,
    error: null as any
  }),

  actions: {
    async fetch() {
      this.loading = true;
      try {
        const response = await api.get('/admin/contracts'); // Use the 'api' instance
        this.contracts = response.data.data;
        return this.contracts;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchById(id: string): Promise<ContractForm | null> {
      this.loading = true;
      try {
        const response = await api.get(`/admin/contracts/${id}`); // Use the 'api' instance
        this.contract = response.data.data;
        return this.contract;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async store(data: any) {
      this.loading = true;
      try {
        const response = await api.post('/admin/contracts', data); // Use the 'api' instance
        // Add the new contract to the contracts array
        if (this.contracts.length) {
          this.contracts.push(response.data.data);
        }
        return response.data.data;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async update(id: number, data: any) {
      this.loading = true;
      try {
        const response = await api.put(`/admin/contracts/${id}`, data); // Use the 'api' instance
        // Update the contract in the contracts array
        const index = this.contracts.findIndex((c: any) => c.id === id);
        if (index !== -1) {
          this.contracts[index] = response.data.data;
        }
        return response.data.data;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async destroy(id: number) {
      this.loading = true;
      try {
        await api.delete(`/admin/contracts/${id}`); // Use the 'api' instance
        // Remove the contract from the contracts array
        this.contracts = this.contracts.filter((c: any) => c.id !== id);
        return true;
      } catch (error) {
        this.error = error;
        throw error;
      } finally {
        this.loading = false;
      }
    }
  }
});
