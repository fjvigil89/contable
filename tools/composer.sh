#!/bin/bash

ENV=${1:-production}
echo Deploying $ENV
ssh -i /root/.ssh/id_rsa root@10.2.1.205 'cd /home/Contable php composer.phar update --prefer-dist -o'

