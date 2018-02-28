Opennet Mitgliedsantrag

Notes for Building:
* build debian package with 'debuild -us -uc'
* debsign -k... (choose key 'gpg --list-secret-keys')
* upload generated .deb file into apt repo: ....
* install .deb on remote server via 'DEPLOY_TARGET=$SERVER make deploy-deb-remote'

Todo for Building:
* test 'make upload-deb', wenn makefilet genutzt wird
* bumbversion testen für einfache Release-Erstellung https://notabug.org/sumpfralle/makefilet/src/master/USAGE.md weil sonst mehrere Changelogs synchron halten per Hand
