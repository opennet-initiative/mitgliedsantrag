Opennet Mitgliedsantrag

Notes for Building:
* build debian package with 'debuild -us -uc'
* upload generated .deb file into apt repo: ....
* install .deb on remote server via 'DEPLOY_TARGET=$SERVER make deploy-deb-remote'

Todo for Building:
* bumbversion testen für einfache Release-Erstellung https://notabug.org/sumpfralle/makefilet/src/master/USAGE.md weil sonst mehrere Changelogs synchron halten per Hand
