const express = require('express');
const multer = require('multer');
const mysql = require('mysql2/promise');
const { Storage } = require('@google-cloud/storage');
const path = require('path');
const app = express();

app.set('view engine', 'ejs');
app.use(express.urlencoded({ extended: true }));
app.use(express.static('public'));

// ⚙️ Multer setup
const upload = multer({ storage: multer.memoryStorage() });

// ⚙️ Google Cloud Storage config
const storage = new Storage({
  keyFilename: 'key.json', // 🔑 Service account key file
});
const bucket = storage.bucket('22028101-image-bucket');

// ⚙️ MySQL config
const dbConfig = {
  host: 'ip-cua-cloud-sql',
  user: 'lehien',
  password: '12345678',
  database: 'website_db',
};

app.get('/', (req, res) => {
  res.redirect('/upload');
});

// 📤 Upload page
app.get('/upload', (req, res) => {
  res.render('upload');
});

app.post('/upload', upload.single('image'), async (req, res) => {
  const { originalname, buffer } = req.file;
  const description = req.body.description;
  const blob = bucket.file(Date.now() + '-' + originalname);
  const blobStream = blob.createWriteStream();

  blobStream.end(buffer);

  blobStream.on('finish', async () => {
    const publicUrl = `https://storage.googleapis.com/${bucket.name}/${blob.name}`;

    const conn = await mysql.createConnection(dbConfig);
    await conn.execute('INSERT INTO images (url, description) VALUES (?, ?)', [publicUrl, description]);
    await conn.end();

    res.redirect('/gallery');
  });
});

// 🖼 Gallery page
app.get('/gallery', async (req, res) => {
  const conn = await mysql.createConnection(dbConfig);
  const [rows] = await conn.execute('SELECT * FROM images ORDER BY id DESC');
  await conn.end();

  res.render('gallery', { images: rows });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log(`Server is running at http://localhost:${PORT}`));