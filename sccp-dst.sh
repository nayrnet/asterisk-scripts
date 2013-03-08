#!/bin/bash
# DST Updating script for chan-sccp-b realtime configuration

function isDST() {
	d=`date +%Y-%d-%m`
	year="${d%%-*}"
	dd="$(echo "${d}"|sed 's/-//g')"

	# 2-nd Sunday of March
	march=$(printf "%d%02d%02d" "${year}" '3' $(cal 3 "${year}" | nawk -v num=2 'FNR>2 && NF==7 && ++cnt==num{print $1}'))

	# 1-st Sunday of November
	nov=$(printf "%d%02d%02d" "${year}" '11' $(cal 11 "${year}" | nawk -v num=1 'FNR>2 && NF==7 && ++cnt==num {print $1}'))

	if [ ${dd} -gt ${march} -a ${dd} -le ${nov} ]; then
	   echo 1
	else
	   echo 0
	fi
}

# Configuration Variables
ASTERISK="/opt/asterisk/sbin/asterisk"
DEVICE=( "SEP000E3834C2BB" "SEP001C58F106DD" "SEP000ED7AC00BD" "SEP000E3834C2EF" "SEP0021A0D97FEA" )

DST=$(isDST)

if [ $DST == 1 ];then
	MONTH=`date +%m`
	if [ $MONTH == "03" ];then
mysql -u asterisk --password=<changeme> asterisk<<EOFMYSQL
UPDATE  sccpdevice SET tzoffset =  '-6' WHERE sccpdevice.name LIKE 'SEP%';
EOFMYSQL
	fi
	if [ $MONTH == "11" ];then
mysql -u asterisk --password=<changeme> asterisk<<EOFMYSQL
UPDATE  sccpdevice SET tzoffset =  '-7' WHERE sccpdevice.name LIKE 'SEP%';
EOFMYSQL
	fi
	for device in "${DEVICE[@]}"; do
		$ASTERISK -rx "sccp reset $device" &>/dev/null
		sleep 10
	done
fi
