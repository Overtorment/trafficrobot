var config = require('./config.json')

var mailin = require('mailin')
var Redis = require('redis')

var storage = Redis.createClient(config.redis.port, config.redis.host, {no_ready_check: true})
storage.auth(config.redis.password, function (err) {
  if (err) throw err
})

storage.on('connect', function () {
  console.log('Connected to Redis')
})

mailin.start({
  port: 25,
  logLevel: 'error', // One of silly, info, debug, warn, error
  disableSpamScore: true,
  disableWebhook: true // Disable the webhook posting.
})

mailin.on('message', function (connection, data) {
  var key = data.to[0].address.split('@')[0]
  storage.lpush('queue', JSON.stringify({key: key, data: data}))
  process.stdout.write('.')
})
