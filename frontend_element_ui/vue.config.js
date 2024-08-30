const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  transpileDependencies: true,
  //outputDir: '../backend/public/webapp',
  outputDir: '../webapp',
  publicPath: './'
})
