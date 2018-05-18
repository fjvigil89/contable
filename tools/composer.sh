#!/bin/bash

ENV=${1:-master}
echo Deploying $ENV
ssh -i /root/.ssh/id_rsa root@10.2.24.193 'cd /home/Contable/master && php ../../composer.phar update --prefer-dist -o'

