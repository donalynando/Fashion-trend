<template>
<!-- Top Navigation Bar -->
<div class="topbar">

<div class="logo">    <i class="fas fa-user"></i> John Doe</div>
<div class="nav-links">
  <router-link to="/editprofile" class="active">Edit Profile</router-link>
  <router-link to="/purchase" class="card">My Purchase</router-link>
</div>
</div>

<!-- Main Content -->
<div class="container">
<button class="back-button" onclick="history.back()">← Back</button>

<div class="page-header" align="center">
  My Profile
</div>

<!-- Profile Info Section -->
<div class="profile-section">
<h2>Manage and protect your account</h2>

<!-- Profile Image Upload -->
<div class="profile-image">
  <img src="/img/user.png" alt="Profile Image" class="profile-picture" />
  <div class="image-upload">
      <label for="fileUpload" class="upload-label">Choose Image</label>
      <input type="file" id="fileUpload" accept=".jpeg, .png" />
      <p class="file-info">Max size: 1 MB | .JPEG, .PNG only</p>
  </div>
</div>

<!-- Profile Information -->
<div class="profile-item">
  <div class="profile-label">Username:</div>
  <div class="input-group">John Doe</div>
  <small>Username can only be changed once.</small>
</div>

<div class="profile-item">
  <div class="profile-label">Name:</div>
  <input type="text" class="input-field" value="John Doe" />
</div>

<div class="profile-item">
  <div class="profile-label">Email:</div>
  <input type="email" class="input-field" placeholder="Add Email" />
</div>

<div class="profile-item">
  <div class="profile-label">Phone Number:</div>
  <input type="text" class="input-field" value="**********91" />
  <a href="#" class="change-link">Change</a>
</div>

<div class="profile-item">
  <div class="profile-label">Gender:</div>
  <select class="select-field">
      <option selected>Male</option>
      <option>Female</option>
      <option>Other</option>
  </select>
</div>

<div class="profile-item">
  <div class="profile-label">Date of Birth:</div>
  <input type="text" class="input-field" placeholder="**/11/20**" />
  <a href="#" class="change-link">Change</a>
</div>

<!-- Save Button -->
<div class="action-buttons">
  <button class="save-button">Save Changes</button>
</div>
</div>

</div>

</template>
<script>
function goBack() {
  window.history.back();
}
</script>
<style scoped>
body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #eef1c4;
      color: #333;
    }

    /* Top Navigation Bar */
    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background-color: #ff0000;
      padding: 10px 20px;
      color: white;
    }

    .topbar .logo {
      font-size: 1.5rem;
      font-weight: bold;
    }

    .topbar .nav-links {
      display: flex;
      gap: 20px;
    }

    .topbar .nav-links a {
      color: white;
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .topbar .nav-links a:hover {
      color: #ffe6e6;
    }

    /* Main Container */
    .container {
      max-width: 900px;
      margin: 20px auto;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
      position: relative;
    }

    /* Section Header */
    .page-header {
      font-size: 2.5rem;
      color: #ff0000;
      margin-bottom: 20px;
    }

     /* Back Button */
     .back-button {
        position: absolute;
        top: 0;
        left: 0;
        background-color: transparent;
        border: none;
        font-size: 1.5rem;
        color: #ff0000;
        cursor: pointer;
        padding: 10px;
        transition: color 0.3s ease;
      }

      .back-button:hover {
        color: #cc0000;
      }

    /* Profile Info Section */
    .profile-section {
      text-align: left;
      margin-top: 50px; /* Adjusted to allow space for back button */
    }

    .profile-item {
      margin-bottom: 10px;
    }

    .profile-label {
      font-weight: bold;
      display: inline-block;
      width: 120px;
    }

    /* Editable Input Fields */
    .input-field, .select-field {
      padding: 8px;
      width: 100%;
      max-width: 300px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .input-group {
      margin-top: 5px;
      font-size: 0.9rem;
      color: #666;
    }



    /* Image Upload */
    .image-upload {
      margin-top: 20px;
      font-size: 0.9rem;
      color: #666;
    }

    .nav-links .active {
        font-weight: bold;
        text-decoration: underline;
      }


      .profile-section {
    max-width: 500px;
    margin: 0 auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background-color: #f9f9f9;
}

h2 {
    text-align: center;
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
}

.profile-image {
    text-align: center;
    margin-bottom: 20px;
    position: relative;
}

.profile-picture {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #ddd;
}

.image-upload {
    text-align: center;
}

.upload-label {
    display: inline-block;
    padding: 5px 10px;
    margin-top: 10px;
    background-color: #007bff;
    color: white;
    border-radius: 5px;
    cursor: pointer;
}

input[type="file"] {
    display: none;
}

.file-info {
    font-size: 12px;
    color: #666;
}

.profile-item {
    margin-bottom: 15px;
}

.profile-label {
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.input-field, .select-field {
    width: 100%;
    padding: 8px;
    border-radius: 5px;
    border: 1px solid #ddd;
    box-sizing: border-box;
}

.change-link {
    color: #007bff;
    font-size: 12px;
    cursor: pointer;
    margin-top: 5px;
    display: inline-block;
}

.action-buttons {
    text-align: center;
}

.save-button {
    padding: 10px 20px;
    border: none;
    background-color: #28a745;
    color: white;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

.save-button:hover {
    background-color: #218838;
}

</style>