Opennet Mitgliedsantrag

Notes for Building:
* build debian package with 'debuild -us -uc'
* debsign -k... (choose key 'gpg --list-secret-keys')
* upload generated .deb file into apt repo: ....
* install .deb on remote server via 'DEPLOY_TARGET=$SERVER make deploy-deb-remote'

build new version
 cat debian/changelog     #get last version number
 dch -v $YOUR_NEW_VERSION_NUMBER$
 dcp -r
 rm ../on-mitgliedsantrag_*.deb   #delete old deb files
 debuild -us -uc
 scp ../on-mitgliedsantrag_*.deb ruri:/var/www/downloads.opennet-initiative.de/debian/
 scp ../on-mitgliedsantrag_*.deb amano:/tmp/
 ssh amano "dpkg -i /tmp/on-mitgliedsantrag_*.deb ; rm /tmp/on-mitgliedsantrag_*.deb"

