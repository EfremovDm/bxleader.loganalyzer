## ������ "���������� �����"
������ ������������ ��� ������ � ������� ����������� ����� ������������ ����.

#### ����������� ������
- ������ ����� ��������� ����������: apache, nginx, mysql, php, cron, mail, bitrix, symfony, yii;
- �������������� ����� �� ����������� ������ � ���������������� ���;
- ����� � ����� �� �����, ���������� �/��� ����������� ���������;
- ������� ������ � �������� ������� ����� (> 1Gb) ����� Linux-���������;
- �������������� ������� � gz-������������ ������� ����� "�� ����", ��� ���������� �� ��������� �����;
- �������������� ������������� ����� � �������� ������������ ���������� �����������;
- ������������������ ������, � ���������� ��������� �� Linux ����� ������������� ��� ��������� ������;
- ������������ �������� ����� Linux ��� �������� ��������� ����������� �������� ����� �������;
- �������������� ��������� ���������� ��������� ��� ���������������� ����� ������������� ������������;
- ��������� ������� ������������� ���� �� �������� ����������� �����;
- �������� ���������� ������, ��������� � ����� ./lib, ����� �������� ��� ���� �������.

#### ����������� ����������:
- ������� ������ �������: 14.0.0, �.�. ���������� ���������� ������� ���� D7.

#### �������� ����������
- LinuxFileProvider - ��������� ��� ������ � ������� ����� Linux-������� ����� exec php-�������;
Linux-�������, ����������� ��� ������: ls, grep, wc, cat, zcat, gzip, head, tail, sed, perl;
��� ��������������� ������� ���������� ��������� ���������� php-������� exec;

- PhpFileProvider - ��������� ��� ������ � ������� �� PHP, �������� ������������������.
��� ������ ��������� ���������� zlib.

#### ��������� ������
����������� ��� ������ � ����� /bitrix/modules/bxleader.loganalyzer ��� /local/modules/bxleader.loganalyzer

#### Cron-�������
������� �������� ������������� ��������� ���� ��� ����������� ������ �����
(�.�. ������� ������ ����� �� ������������ ����������)

��������:
- ��� ����� ���� �������, ��� ��������������� �� ��������� ������ �������� ������������� �� ���!
- ����� �������������� ��������� ��������� ������ ���������� � ��� ����� (������� ls -l),
�.�. �������� ������, ����� ������ �������� �� �� ��� root.
- �������� ����������� ������ � mysql, ��� ������������� � �������.
```
# ��������� ���������� �����
* */1 * * * chown root:bitrix /var/log
* */1 * * * chown -R root:bitrix /var/log/httpd
* */1 * * * chown -R root:bitrix /var/log/nginx
* */1 * * * chown -R mysql:bitrix /var/log/mysql
* */1 * * * chown root:bitrix /var/log/cron*
* */1 * * * chown root:bitrix /var/log/maillog*

# ��������� ����
* */1 * * * find /var/log/httpd -type d -exec chmod 750 {} \;
* */1 * * * find /var/log/httpd -type f -exec chmod 640 {} \;
* */1 * * * find /var/log/nginx -type d -exec chmod 750 {} \;
* */1 * * * find /var/log/nginx -type f -exec chmod 640 {} \;
* */1 * * * find /var/log/php -type d -exec chmod 750 {} \;
* */1 * * * find /var/log/php -type f -exec chmod 640 {} \;
* */1 * * * find /var/log/mysql -type d -exec chmod 750 {} \;
* */1 * * * find /var/log/mysql -type f -exec chmod 660 {} \;
* */1 * * * find /var/log/cron* -type f -exec chmod 640 {} \;
* */1 * * * find /var/log/maillog* -type f -exec chmod 640 {} \;
```

������������� �����, ���������� �� ������ ������� ����� rsync
```
ssh-keygen -t rsa
ssh-copy-id -i /root/.ssh/id_rsa.pub bitrix@10.2.3.4

0 1 * * * rsync -r -e "ssh" /var/log/mysql/ bitrix@10.2.3.4:/var/log/mysql/
```

