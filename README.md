Opennet Mitgliedsantrag

Notes for Building:
* build debian package with 'debuild -us -uc'
* debsign -k... (choose key 'gpg --list-secret-keys')
* upload generated .deb file into apt repo: ....
* install .deb on remote server via 'DEPLOY_TARGET=$SERVER make deploy-deb-remote'

build new version
 dch -i
 dhcp -r
 debuild -us -uc
 scp ../on-mitgliedsantrag_*.deb amano:/tmp/
 ssh amano "dpkg -i /tmp/on-mitgliedsantrag_*.deb ; rm /tmp/on-mitgliedsantrag_*.deb"

Todo for Building:
* make dist-deb
* deb-Paket auf einem entfernten Host installieren:
   make deploy-deb-remote DEPLOY_TARGET=root@example.on
   Dies aber erst machen, wenn apt repo fertig ist, weil hier auch signing
    und ähnliches nötig ist.
* bumbversion testen für einfache Release-Erstellung https://notabug.org/sumpfralle/makefilet/src/master/USAGE.md weil sonst mehrere Changelogs synchron halten per Hand
* test 'make upload-deb', wenn apt repo fertig ist
