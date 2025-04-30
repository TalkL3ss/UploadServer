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
✔️ View uploaded files  
✔️ Download files dynamically  
✔️ Interactive UI  

---

## 🔧 Installation & Setup

### **1️⃣ Clone the Repository**
git clone https://github.com/TalkL3ss/UploadServer.git
cd YOUR_REPOSITORY


### **2️⃣ Install Dependencies**
Run the installation script:
chmod +x install_prereq.sh
./install_prereq.sh

### **3️⃣ Start the Servers**
Start both **WebSocket PHP server** and **Python HTTP server**:
chmod +x start_servers.sh
./start_servers.sh

### **4️⃣ Access the Website**
Once the servers are running, open:
http://YOUR_MACHINE_IP:8000

---

## 🛑 Stopping the Servers
To stop both the **WebSocket PHP server** and the **Python HTTP server**, run:
chmod +x stop_servers.sh
./stop_servers.sh
---

## 📂 File Upload & Download
✔️ **Upload:** Select a file and press **Upload**  
✔️ **View Files:** Click **Show Uploaded Files**  
✔️ **Download:** Click **Download** next to a file  

All uploaded files are stored in:
uploads/


---

## 🛠 Built With
- **PHP** (WebSocket Server via Ratchet)
- **Python** (HTTP Server)
- **JavaScript & HTML** (Client-side UI)

---

## 💡 Future Improvements
🔹 Multi-file upload support  
🔹 Progress tracking for uploads  
🔹 Secure login system  

---

## 👨‍💻 Author
Created by **Bing AI**
