# WebSocket File Upload System

## 📌 Project Overview
This project provides a **WebSocket-based file upload system** using:
- **PHP WebSocket Server** (via Ratchet)
- **Python HTTP Server** (serving the frontend)
- **Client-side HTML & JavaScript** (for file upload and download)

Users can **upload, view, and download files** through an interactive web interface with WebSocket connectivity.

---

## 🚀 Features
✔️ Upload files via WebSocket  
✔️ Password-protected WebSocket server  
✔️ Interactive UI  

---

## 🔧 Installation & Setup

### **1️⃣ Clone the Repository**
```bash
git clone https://github.com/TalkL3ss/UploadServer.git
cd ./UploadServer
```

### **2️⃣ Install Dependencies**
Run the installation script:
```bash
chmod +x install_prereq.sh
./install_prereq.sh
```

During installation, you will be prompted to set a password for the WebSocket server. If you leave it blank, the default password (`securepassword`) will be used.

### **3️⃣ Start the Servers**
Start both **WebSocket PHP server** and **Python HTTP server**:
```bash
chmod +x start_servers.sh
./start_servers.sh
```

---

### 🛑 Stopping the Servers
To stop the servers, run:
```bash
chmod +x kill-servers.sh
./kill-servers.sh
```

---

## 📂 Usage
✔️ **Upload:** Select a file, enter the password, and click **Upload**.  
✔️ **View Files:** Click **Show Uploaded Files**.  
✔️ **Download:** Click **Download** next to a file.  

Default uploads directory:
```
uploads/
```

---

## 🛠 Built With
- **PHP** (WebSocket Server via Ratchet)
- **Python** (HTTP Server)
- **JavaScript & HTML** (Client-side UI)
