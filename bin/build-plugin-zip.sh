#!/usr/bin/env bash
#
# Creates a release build ZIP of the plugin.
#
# Usage: bin/build-plugin-zip.sh
#
# Inspired by https://github.com/WordPress/gutenberg/blob/8c80c0e2be01b4868aa55727b6cfcfca0ef438ba/bin/build-plugin-zip.sh

# Exit if any command fails.
set -e

# Change to the expected directory.
cd "$(dirname "$0")"
cd ..

# Enable nicer messaging for build status.
BLUE_BOLD='\033[1;34m';
GREEN_BOLD='\033[1;32m';
RED_BOLD='\033[1;31m';
YELLOW_BOLD='\033[1;33m';
COLOR_RESET='\033[0m';
error () {
	echo -e "\n${RED_BOLD}$1${COLOR_RESET}\n"
}
status () {
	echo -e "\n${BLUE_BOLD}$1${COLOR_RESET}\n"
}
success () {
	echo -e "\n${GREEN_BOLD}$1${COLOR_RESET}\n"
}
warning () {
	echo -e "\n${YELLOW_BOLD}$1${COLOR_RESET}\n"
}

status "💃 Time to build the Admin Debug Tools plugin ZIP file 🕺"

if [ -z "$NO_CHECKS" ]; then
	# Make sure there are no changes in the working tree. Release builds should be
	# traceable to a particular commit and reliably reproducible.
	changed=
	if ! git diff --exit-code > /dev/null; then
		changed="file(s) modified"
	elif ! git diff --cached --exit-code > /dev/null; then
		changed="file(s) staged"
	fi
	if [ ! -z "$changed" ]; then
		git status
		error "ERROR: Cannot build plugin zip with dirty working tree. ☝️
		Commit your changes and try again."
		# exit 1
	fi

	# # Do a dry run of the repository reset. Prompting the user for a list of all
	# # files that will be removed should prevent them from losing important files!
	# status "Resetting the repository to pristine condition. ✨"
	# to_clean=$(git clean -xdf --dry-run)
	# if [ ! -z "$to_clean" ]; then
	# 	echo $to_clean
	# 	warning "🚨 About to delete everything above! Is this okay? 🚨"
	# 	echo -n "[y]es/[N]o: "
	# 	read answer
	# 	if [ "$answer" != "${answer#[Yy]}" ]; then
	# 		# Remove ignored files to reset repository to pristine condition. Previous
	# 		# test ensures that changed files abort the plugin build.
	# 		status "Cleaning working directory... 🛀"
	# 		git clean -xdf
	# 	else
	# 		error "Fair enough; aborting. Tidy up your repo and try again. 🙂"
	# 		exit 1
	# 	fi
	# fi
fi

# Run the build.
status "Installing dependencies... 📦"
npm cache verify
npm ci
status "Generating build... 👷‍♀️"
npm run build
status "Building PHP files... 🏗️"
composer install --no-dev --optimize-autoloader

build_files=$(
	ls build/*.{js,css,asset.php}
)

# Generate the plugin zip file.
status "Creating archive... 🎁"
zip -r admin-debug-tools.zip \
	admin-debug-tools.php \
	uninstall.php \
	assets \
	includes \
	languages \
	vendor \
	$build_files \
	readme.txt \
	-x '**/.*'

mkdir -p release
mv admin-debug-tools.zip release/admin-debug-tools.zip

success "Done. You've built Admin Debug Tools! 🎉 "

status "Restoring Dev PHP dependencies... 🏗️"
composer install
