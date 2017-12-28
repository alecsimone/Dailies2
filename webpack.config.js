//webpack.config.js
var webpack = require('webpack');
var ExtractTextPlugin = require('extract-text-webpack-plugin');
var OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin');

var version = '-v1.10';

module.exports = {
	devtool: 'cheap-module-source-map',
    entry: {
    	main: "./Entries/main-entry.js",
    	secretGarden: "./Entries/secret-garden-entry.js",
    	live: "./Entries/live-entry.js",
    	schedule: "./Entries/schedule-entry.js",
    	global: "./Entries/global-entry.js", //Global must be kept last because it contains all the CSS files to be combined
    },
	output: {
		path: __dirname + "/Bundles",
		filename: "[name]-bundle" + version + ".js"
	},
	watch: true,
	module: {
		loaders: [
			{
				test: /\.scss$/, 
				loader: ExtractTextPlugin.extract({
					fallback: 'style-loader',
					use: ['css-loader', 'sass-loader'],
				}), 
				exclude: /node_modules/},
			{
                test: /\.jsx?$/,
                loaders: 'babel-loader',
                exclude: /node_modules/,
                query: {
                	presets: ['es2015', 'react']
                }
            },
            {
                test: /\.js$/,
                loaders: 'babel-loader',
                exclude: /node_modules/,
                query: {
                	presets: ['es2015']
                }
            }
		]
	},
		plugins: [
			new ExtractTextPlugin("../style" + version + ".css"),
			new OptimizeCssAssetsPlugin(),
		/*	new webpack.DefinePlugin({
				'process.env': {
					'NODE_ENV': JSON.stringify('production')
				}
			}),
			new webpack.optimize.AggressiveMergingPlugin(),
			new webpack.optimize.UglifyJsPlugin(), */
		],
};