#!/usr/bin/env node

const fs = require("fs");
const path = require("path");

const chalk = require("chalk");
const archiver = require("archiver");
const recursive = require("recursive-readdir");
const prettyBytes = require("pretty-bytes");

const excludes = [
	".circleci",
	".DS_Store",
	".editorconfig",
	".eslintignore",
	".eslintrc.js",
	".git",
	".gitattributes",
	".github",
	".gitignore",
	".scripts",
	".stylelintrc.json",
	"*.zip",
	".stylelintignore",
	".vscode",
	".eslintrc",
	".nvmrc",
	"Makefile",
	"tests",
	"phpunit.xml.dist",
	"composer.json",
	"composer.lock",
	"node_modules",
	"package-lock.json",
	"package.json",
	"phpcs.xml.dist",
	"*scss",
	"*.md",
	'webpack.config.js'
];

// Creates a file to stream archive data to.
// Uses the name in package.json, such as 'child-theme.1.1.0.zip'.
const slug = process.env.THEME_SLUG || process.env.npm_package_name;
const version = process.env.THEME_VERSION || process.env.npm_package_version;
const fileName = process.env.VERSION_ARTIFACT_FILE || `${slug}.${version}.zip`;

const output = fs.createWriteStream(fileName);

const archive = archiver("zip", {
	zlib: { level: 9 } // Best compression.
});

/**
 * Sets up the file output stream and archive.
 */
const setupZipArchive = function() {
	// Listens for all archive data to be written.
	// Report the zip name and size.
	output.on("close", function() {
		const fileSize = prettyBytes(archive.pointer());
		console.log(chalk`{cyan Created ${fileName}, ${fileSize}}`);
	});

	// Displays warnings during archiving.
	archive.on("warning", function(err) {
		if (err.code === "ENOENT") {
			console.log(err);
		} else {
			throw err;
		}
	});

	// Catches errors during archiving.
	archive.on("error", function(err) {
		throw err;
	});

	// Pipes archive data to the file.
	archive.pipe(output);
};

/**
 * Loops through theme directory, omitting files in the `exclude` array.
 * Adds each file to the zip archive.
 */
const zipFiles = function() {
	recursive(process.cwd(), excludes, function(err, files) {
		let relativePath;

		console.log(chalk`{cyan Making zip file}`);
		files.forEach(function(filePath) {
			relativePath = path.relative(process.cwd(), filePath);
			archive.file(filePath, {
				name: `${process.env.npm_package_name}/${relativePath}`
			});
		});

		archive.finalize();
	});
};

setupZipArchive();
zipFiles();