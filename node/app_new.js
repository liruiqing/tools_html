var express = require('express')
var multer  = require('multer')
var func = require('./func.js');
//var upload = multer({ dest: 'uploads/' })
var storage = multer.diskStorage({
    destination: function (req, file, cb) {
        cb(null, 'uploads/')
    },
    filename: function (req, file, cb) {
        cb(null, file.fieldname + '-' + Date.now()+func.get_dot(file.originalname))
    }
})

var upload = multer({ storage: storage })
var app = express();

app.post('/profile', upload.fields([
    { name: 'user_img', maxCount: 1 },
    { name: 'b', maxCount: 1 }
]), function (req, res, next) {
    console.log(req.files);
    res.send('122222')
    // req.file is the `avatar` file
    // req.body will hold the text fields, if there were any
})

app.post('/photos/upload', upload.array('photos', 12), function (req, res, next) {
    // req.files is array of `photos` files
    // req.body will contain the text fields, if there were any
})

var cpUpload = upload.fields([{ name: 'avatar', maxCount: 1 }, { name: 'gallery', maxCount: 8 }])
app.post('/cool-profile', cpUpload, function (req, res, next) {
    // req.files is an object (String -> Array) where fieldname is the key, and the value is array of files
    //
    // e.g.
    //  req.files['avatar'][0] -> File
    //  req.files['gallery'] -> Array
    //
    // req.body will contain the text fields, if there were any
});
var server = app.listen(3001, function () {
    console.log('Listening on port %d', server.address().port);
});

