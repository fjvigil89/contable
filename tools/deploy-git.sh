#!/bin/bash

ENV=${1:-master}
echo Deploying $ENV
ssh -i /root/.ssh/id_rsa root@10.2.1.205 'r10k deploy environment -p -v -c /etc/r10k.yaml'

