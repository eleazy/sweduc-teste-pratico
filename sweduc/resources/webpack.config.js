const path = require('path');
const TerserJSPlugin = require('terser-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const ReactRefreshWebpackPlugin = require('@pmmmwh/react-refresh-webpack-plugin');
const webpack = require('webpack');

const isDevelopment = process.env.NODE_ENV !== 'production';

module.exports = {
  mode: isDevelopment ? 'development' : 'production',
  devtool: process.env.NODE_ENV !== "production" && 'source-map',

  devServer: {
    static: {
      directory: path.resolve(__dirname, '../public/assets/'),
    },
    devMiddleware: {
      publicPath: '/assets/', // Moved under devMiddleware as of v5
    },
    hot: true,
    // disableHostCheck: true,
    allowedHosts: [
      'localhost',
    ],
    headers: {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Headers': '*',
        'Access-Control-Allow-Methods': '*',
    },
    proxy: {
      '/': 'http://localhost/'
    }
  },

  entry: {
    // Principais
    app: './js/app', // React app
    index: './js/index.js', // Novas páginas
    login: './js/login.js', // Pagina de login
    guest: './js/guest.js', // Acesso externo

    // Páginas em react ou/e scripts auxiliares de páginas
    recebeCartao: './js/page/recebeCartao.js',
    preMatricula: './js/page/preMatricula.js',
  },

  output: {
    path: path.resolve(__dirname, '../public/assets/'),
    publicPath: '/assets/',
  },

  optimization: {
    minimizer: [
        new TerserJSPlugin({}),
        // new CssMinimizerPlugin()
    ],
  },

  plugins: [
    new CleanWebpackPlugin(),
    new MiniCssExtractPlugin({
      filename: '[name].css',
      chunkFilename: '[id].css',
    })
  ].concat([
    isDevelopment && new webpack.HotModuleReplacementPlugin(),
    isDevelopment && new ReactRefreshWebpackPlugin(),
  ].filter(Boolean)),

  module: {
    rules: [
        {
            test: /\.(scss|css)$/,
            use: [
                MiniCssExtractPlugin.loader,
                'css-loader', // translates CSS into CommonJS modules
                'postcss-loader', // Run post css actions
                'sass-loader', // compiles Sass to CSS
            ]
        },
        {
            test: /\.(ttf|eot|svg|woff(2)?)(\?[a-z0-9]+)?$/,
            loader: 'file-loader',
            options: {
                outputPath: 'fonts',
            },
        },
        {
            test: /\.(png|jpe?g|gif)$/i,
            use: [
                {
                    loader: 'file-loader',
                },
            ],
        },
        {
            test: /\.(js|jsx|ts|tsx)$/,
            exclude: /node_modules/,
            use: [
              {
                loader: require.resolve('babel-loader'),
                options: {
                  plugins: [
                    isDevelopment && require.resolve('react-refresh/babel'),
                  ].filter(Boolean),
                },
              },
            ],
        },
    ],
  },
};
