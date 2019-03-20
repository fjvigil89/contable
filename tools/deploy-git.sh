#!/bin/bash

ENV=${1:-master}
echo Deploying $ENV
ssh -i /root/.ssh/id_rsa root@wh-bk.upr.edu.cu 'r10k deploy environment -p -v -c /etc/r10k.yaml'

