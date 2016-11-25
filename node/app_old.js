/*查找数据表更新数据*/
var http = require('http');
var mysql  = require('mysql');
var config = require('./config');



var pool = mysql.createPool({
    connectionLimit:5,//连接池最多可以创建连接数
    host     : config.host,
    user     : config.user,
    password :config.password,
    database : config.database,
    queueLimit:0, // 队伍中等待连接的最大数量，0为不限制。
    /*bigNumberStrings: true,
    dateStrings:true,
    stringifyObjects:true*/
});
pool.getConnection(function(err, connection) {
    connection.query("select * from cars where id=12", function(err, rows) {
        connection.release();
        if(err){
            throw err;
        }
       console.log(JSON.stringify(rows))
        for(var i=0;i<rows.length;i++){
            var name = rows[i]['name'];
            var engine = rows[i]['engine'];
            var vin = rows[i]['vin'];
            var licenseno = rows[i]['licenseno'];
            var user_id = rows[i]['id'];
            (function(name,engine,vin, licenseno,user_id){
                send(name,engine,vin, licenseno,function(data){
                    console.log(user_id);
                    data = JSON.parse(data);
                    pool.getConnection(function(err, connection) {
                        console.log([data.brand, data.family_name,data.model_desc,data.model_name,data.price,data.seat,data.first_date,user_id])
                        connection.query("update cars set brand=?,family_name=?,model_desc=?,model_name=?,price=?,seat=?,first_date=? where id=?",[data.brand, data.family_name,data.model_desc,data.model_name,data.price,data.seat,data.first_date,user_id], function(err, rows) {
                            connection.release();
                            console.log(rows);
                        })
                    });

                });
            })(name,engine,vin, licenseno,user_id)

        }

    });
});

function send(name,engine,vin,licenseno,callback){
   // var url = 'http://182.92.84.62:3001/server/search_car_model?token=*32AE42D4EBE3105CC530663F8555625D6A35C690&name='+name+'&engine='+engine+'&vin='+vin+'&licenseno='+licenseno;
        var options = {
            hostname: '182.92.84.62',
            //hostname: '192.168.20.196',
            port: 3001,
            timeout:150000,
            path: '/server/search_car_model?token=*32AE42D4EBE3105CC530663F8555625D6A35C690&name='+encodeURI(name)+'&engine='+encodeURI(engine)+'&vin='+encodeURI(vin)+'&licenseno='+encodeURI(licenseno),
            method: 'GET'
        };

        var req = http.request(options, function (res) {
            res.setEncoding('utf8');
            res.on('data', function (chunk) {
                console.log(chunk)
                callback(chunk);
            });
        });
        req.on('error', function (e) {
            console.log('problem with request: ' + e.message);
        });
        req.end();
}
