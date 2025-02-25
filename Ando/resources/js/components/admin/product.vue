<template>
  <div class="products-container">
    <div class="header-actions">
      <h2>Manage Products</h2>
      <button class="add-product-btn" @click="openAddModal">
        <i class="fas fa-plus"></i> Add New Product
      </button>
    </div>
    
    <div v-if="error" class="alert alert-error">
      {{ error }}
    </div>

    <div v-if="loading" class="loading-spinner">
      Loading products...
    </div>
    
    <div v-else class="products-table-container">
      <table class="products-table">
        <thead>
          <tr>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Image</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id">
            <td>#{{ product.id }}</td>
            <td>{{ product.name }}</td>
            <td>{{ product.description || 'No description' }}</td>
            <td>₱{{ formatPrice(product.price) }}</td>
            <td>
              <span :class="['stock-badge', getStockClass(product.stock)]">
                {{ product.stock }}
              </span>
            </td>
            <td>
              <div class="product-image-container">
                <img 
                  v-if="product.image" 
                  :src="product.image" 
                  alt="Product image" 
                  class="product-thumbnail"
                  @click="openImagePreview(product.image)"
                />
                <span v-else class="no-image">
                  <i class="fas fa-image"></i>
                  No image
                </span>
              </div>
            </td>
            <td>
              <span :class="['status', product.is_active ? 'active' : 'inactive']">
                {{ product.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="actions">
              <button class="edit-btn" @click="openEditModal(product)">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button class="delete-btn" @click="deleteProduct(product.id)">
                <i class="fas fa-trash"></i> Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Add/Edit Product Modal -->
    <div v-if="showModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ isEditing ? 'Edit Product' : 'Add New Product' }}</h3>
          <button class="close-btn" @click="closeModal">&times;</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitForm" enctype="multipart/form-data">
            <div class="form-group">
              <label for="name">Product Name</label>
              <input 
                type="text" 
                id="name" 
                v-model="formData.name" 
                required
                :class="{ 'error': formErrors.name }"
              >
              <span class="error-message" v-if="formErrors.name">{{ formErrors.name }}</span>
            </div>

            <div class="form-group">
              <label for="description">Description</label>
              <textarea 
                id="description" 
                v-model="formData.description" 
                required
                :class="{ 'error': formErrors.description }"
                rows="3"
              ></textarea>
              <span class="error-message" v-if="formErrors.description">{{ formErrors.description }}</span>
            </div>

            <div class="form-group">
              <label for="price">Price (₱)</label>
              <input 
                type="number" 
                id="price" 
                v-model="formData.price" 
                step="0.01" 
                min="0" 
                required
                :class="{ 'error': formErrors.price }"
              >
              <span class="error-message" v-if="formErrors.price">{{ formErrors.price }}</span>
            </div>

            <div class="form-group">
              <label for="stock">Stock</label>
              <div class="stock-input">
                <input 
                  type="number" 
                  id="stock" 
                  v-model="formData.stock" 
                  min="0" 
                  required
                  :class="{ 'error': formErrors.stock }"
                >
                <div class="stock-actions">
                  <button type="button" @click="adjustStock(1)" class="stock-btn">
                    <i class="fas fa-plus"></i>
                  </button>
                  <button type="button" @click="adjustStock(-1)" class="stock-btn" :disabled="formData.stock <= 0">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <span class="error-message" v-if="formErrors.stock">{{ formErrors.stock }}</span>
            </div>

            <div class="form-group">
              <label for="image">Product Image</label>
              <div class="image-upload-container">
                <div class="image-preview" v-if="imagePreview || formData.image">
                  <img :src="imagePreview || formData.image" alt="Preview" class="preview-image">
                  <button type="button" class="remove-image" @click="removeImage">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <div class="upload-area" v-else @click="triggerFileInput" @dragover.prevent @drop.prevent="handleFileDrop">
                  <input 
                    type="file" 
                    ref="fileInput" 
                    @change="handleFileChange" 
                    accept="image/*"
                    class="file-input"
                    :class="{ 'error': formErrors.image }"
                  >
                  <i class="fas fa-cloud-upload-alt"></i>
                  <p>Click or drag image to upload</p>
                  <p class="file-limits">Supported: JPG, PNG, GIF (max 2MB)</p>
                </div>
              </div>
              <span class="error-message" v-if="formErrors.image">{{ formErrors.image }}</span>
            </div>

            <div class="form-group">
              <label class="checkbox-label">
                <input 
                  type="checkbox" 
                  v-model="formData.is_active"
                >
                Product is active
              </label>
            </div>

            <div class="form-actions">
              <button type="button" class="cancel-btn" @click="closeModal">Cancel</button>
              <button type="submit" class="submit-btn" :disabled="isSubmitting">
                {{ isSubmitting ? 'Saving...' : (isEditing ? 'Update Product' : 'Add Product') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Image Preview Modal -->
    <div v-if="showImagePreview" class="image-preview-modal" @click="closeImagePreview">
      <div class="image-preview-content" @click.stop>
        <button class="close-preview" @click="closeImagePreview">&times;</button>
        <img :src="previewImage" alt="Product image preview" class="full-image">
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

export default {
  name: 'AdminProducts',
  
  setup() {
    const router = useRouter();
    const products = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const showModal = ref(false);
    const isEditing = ref(false);
    const isSubmitting = ref(false);
    const currentProductId = ref(null);
    const fileInput = ref(null);
    const imagePreview = ref(null);
    const showImagePreview = ref(false);
    const previewImage = ref(null);

    // Form data
    const formData = reactive({
      name: '',
      description: '',
      price: 0,
      stock: 0,
      image: null,
      is_active: true,
      remove_image: false
    });

    const formErrors = reactive({
      name: null,
      description: null,
      price: null,
      stock: null,
      image: null
    });

    // Check authentication
    const checkAuth = () => {
      const token = localStorage.getItem('admin_token');
      if (!token) {
        router.push({ name: 'admin-login' });
        return false;
      }
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      return true;
    };

    // Fetch products
    const fetchProducts = async () => {
      if (!checkAuth()) return;
      
      try {
        loading.value = true;
        const response = await axios.get('/api/admin/products');
        products.value = response.data;
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to load products';
        if (err.response?.status === 401) {
          router.push({ name: 'admin-login' });
        }
      } finally {
        loading.value = false;
      }
    };

    // Handle file selection
    const handleFileChange = (event) => {
      const file = event.target.files[0];
      if (file) {
        if (file.size > 2 * 1024 * 1024) {
          formErrors.image = 'Image must be less than 2MB';
          return;
        }
        formData.image = file;
        createImagePreview(file);
      }
    };

    // Create image preview
    const createImagePreview = (file) => {
      const reader = new FileReader();
      reader.onload = (e) => {
        imagePreview.value = e.target.result;
      };
      reader.readAsDataURL(file);
    };

    // Handle file drop
    const handleFileDrop = (event) => {
      const file = event.dataTransfer.files[0];
      if (file && file.type.startsWith('image/')) {
        if (file.size > 2 * 1024 * 1024) {
          formErrors.image = 'Image must be less than 2MB';
          return;
        }
        formData.image = file;
        createImagePreview(file);
      }
    };

    // Trigger file input click
    const triggerFileInput = () => {
      fileInput.value.click();
    };

    // Remove image
    const removeImage = () => {
      formData.image = null;
      imagePreview.value = null;
      formData.remove_image = true;
      if (fileInput.value) {
        fileInput.value.value = '';
      }
    };

    // Open add modal
    const openAddModal = () => {
      isEditing.value = false;
      currentProductId.value = null;
      resetForm();
      showModal.value = true;
    };

    // Open edit modal
    const openEditModal = (product) => {
      isEditing.value = true;
      currentProductId.value = product.id;
      Object.assign(formData, {
        name: product.name,
        description: product.description,
        price: product.price,
        stock: product.stock,
        image: product.image,
        is_active: product.is_active,
        remove_image: false
      });
      showModal.value = true;
    };

    // Close modal
    const closeModal = () => {
      showModal.value = false;
      resetForm();
    };

    // Reset form
    const resetForm = () => {
      Object.assign(formData, {
        name: '',
        description: '',
        price: 0,
        stock: 0,
        image: null,
        is_active: true,
        remove_image: false
      });
      imagePreview.value = null;
      Object.keys(formErrors).forEach(key => formErrors[key] = null);
    };

    // Submit form
    const submitForm = async () => {
      if (!checkAuth()) return;

      try {
        isSubmitting.value = true;
        const formDataToSend = new FormData();
        
        // Append all form fields with proper type handling
        Object.keys(formData).forEach(key => {
          if (key === 'image' && formData[key] instanceof File) {
            formDataToSend.append('image', formData[key]);
          } else if (key === 'is_active') {
            formDataToSend.append('is_active', formData[key] ? '1' : '0');
          } else if (key !== 'image') {
            formDataToSend.append(key, formData[key]);
          }
        });

        const response = isEditing.value
          ? await axios.post(`/api/admin/products/${currentProductId.value}`, formDataToSend, {
              headers: { 'Content-Type': 'multipart/form-data' }
            })
          : await axios.post('/api/admin/products', formDataToSend, {
              headers: { 'Content-Type': 'multipart/form-data' }
            });

        await fetchProducts();
        closeModal();
      } catch (err) {
        console.error('Error response:', err.response?.data);
        
        if (err.response?.data?.errors) {
          Object.keys(err.response.data.errors).forEach(key => {
            formErrors[key] = err.response.data.errors[key][0];
          });
        } else {
          error.value = err.response?.data?.message || 'Failed to save product';
        }
      } finally {
        isSubmitting.value = false;
      }
    };

    // Delete product
    const deleteProduct = async (id) => {
      if (!checkAuth()) return;
      
      if (!confirm('Are you sure you want to delete this product?')) return;

      try {
        await axios.delete(`/api/admin/products/${id}`);
        await fetchProducts();
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to delete product';
      }
    };

    // Adjust stock
    const adjustStock = (amount) => {
      const newStock = Number(formData.stock) + amount;
      if (newStock >= 0) {
        formData.stock = newStock;
      }
    };

    // Format price
    const formatPrice = (price) => {
      return Number(price).toFixed(2);
    };

    // Get stock class
    const getStockClass = (stock) => {
      if (stock <= 0) return 'out-of-stock';
      if (stock <= 10) return 'low-stock';
      return 'in-stock';
    };

    // Image preview functions
    const openImagePreview = (imageUrl) => {
      previewImage.value = imageUrl;
      showImagePreview.value = true;
    };

    const closeImagePreview = () => {
      showImagePreview.value = false;
      previewImage.value = null;
    };

    onMounted(() => {
      fetchProducts();
    });

    return {
      products,
      loading,
      error,
      showModal,
      isEditing,
      isSubmitting,
      formData,
      formErrors,
      fileInput,
      imagePreview,
      showImagePreview,
      previewImage,
      openImagePreview,
      closeImagePreview,
      openAddModal,
      openEditModal,
      closeModal,
      submitForm,
      deleteProduct,
      adjustStock,
      formatPrice,
      getStockClass,
      handleFileChange,
      handleFileDrop,
      triggerFileInput,
      removeImage
    };
  }
};
</script>

<style scoped>
.products-container {
  padding: 20px;
}

.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.header-actions h2 {
  margin: 0;
  color: #333;
  font-size: 1.5rem;
}

.add-product-btn {
  background: #FF7A00;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.add-product-btn:hover {
  background: #e66e00;
}

.products-table-container {
  background: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.products-table {
  width: 100%;
  border-collapse: collapse;
}

.products-table th,
.products-table td {
  padding: 15px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.products-table th {
  background: #f9f9f9;
  font-weight: 500;
  color: #666;
}

.products-table tr:hover {
  background: #f5f5f5;
}

.stock-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: 500;
  font-size: 0.9rem;
}

.stock-badge.out-of-stock {
  background: #ffebee;
  color: #d32f2f;
}

.stock-badge.low-stock {
  background: #fff3e0;
  color: #f57c00;
}

.stock-badge.in-stock {
  background: #e8f5e9;
  color: #388e3c;
}

.status {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
  text-transform: capitalize;
}

.status.active {
  background: #e8f5e9;
  color: #388e3c;
}

.status.inactive {
  background: #ffebee;
  color: #d32f2f;
}

.product-image-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 60px;
  height: 60px;
  margin: 0 auto;
}

.product-thumbnail {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 4px;
  cursor: pointer;
  transition: transform 0.2s;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.product-thumbnail:hover {
  transform: scale(1.1);
}

.no-image {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #999;
  font-size: 0.8rem;
}

.no-image i {
  font-size: 1.5rem;
  margin-bottom: 4px;
}

.actions {
  display: flex;
  gap: 8px;
}

.edit-btn,
.delete-btn {
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  font-size: 0.85rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 4px;
  transition: background-color 0.3s ease;
}

.edit-btn {
  background: #e3f2fd;
  color: #1976d2;
}

.edit-btn:hover {
  background: #bbdefb;
}

.delete-btn {
  background: #ffebee;
  color: #d32f2f;
}

.delete-btn:hover {
  background: #ffcdd2;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 10px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 20px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
}

.modal-body {
  padding: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #333;
  font-weight: 500;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.9rem;
}

.form-group input.error,
.form-group textarea.error {
  border-color: #d32f2f;
}

.error-message {
  color: #d32f2f;
  font-size: 0.85rem;
  margin-top: 4px;
}

.stock-input {
  display: flex;
  gap: 10px;
  align-items: center;
}

.stock-actions {
  display: flex;
  gap: 4px;
}

.stock-btn {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  background: white;
  cursor: pointer;
  transition: all 0.3s ease;
}

.stock-btn:hover:not(:disabled) {
  background: #f5f5f5;
}

.stock-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.cancel-btn,
.submit-btn {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.cancel-btn {
  background: #f5f5f5;
  color: #333;
}

.cancel-btn:hover {
  background: #e0e0e0;
}

.submit-btn {
  background: #FF7A00;
  color: white;
}

.submit-btn:hover:not(:disabled) {
  background: #e66e00;
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.loading-spinner {
  text-align: center;
  padding: 40px;
  color: #666;
}

.alert {
  padding: 12px 20px;
  border-radius: 4px;
  margin-bottom: 20px;
}

.alert-error {
  background: #ffebee;
  color: #d32f2f;
  border: 1px solid #ffcdd2;
}

.image-upload-container {
  width: 100%;
  margin-top: 10px;
}

.image-preview {
  position: relative;
  width: 200px;
  height: 200px;
  margin-bottom: 10px;
}

.preview-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
}

.remove-image {
  position: absolute;
  top: -10px;
  right: -10px;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.upload-area {
  border: 2px dashed #ccc;
  border-radius: 8px;
  padding: 20px;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.3s;
}

.upload-area:hover {
  border-color: #007bff;
}

.upload-area i {
  font-size: 2rem;
  color: #666;
  margin-bottom: 10px;
}

.file-input {
  display: none;
}

.file-limits {
  font-size: 0.8rem;
  color: #666;
  margin-top: 5px;
}

/* Image Preview Modal */
.image-preview-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.image-preview-content {
  position: relative;
  max-width: 90%;
  max-height: 90vh;
  background: white;
  padding: 10px;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.close-preview {
  position: absolute;
  top: -15px;
  right: -15px;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: #dc3545;
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.full-image {
  max-width: 100%;
  max-height: 80vh;
  object-fit: contain;
  border-radius: 4px;
}
</style>