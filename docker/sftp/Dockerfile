FROM alpine:3.13.6
MAINTAINER Samuel RODIER

# Install the openssh server from the packets manager
RUN apk add --update --no-cache openssh openssh-sftp-server

# Customise the SSH server banner
RUN echo 'Welcome!' > /etc/motd

# create the host keys
COPY host/ssh_host_dsa_key /etc/ssh/ssh_host_dsa_key
COPY host/ssh_host_rsa_key /etc/ssh/ssh_host_rsa_key

# create sftps users
RUN addgroup processuswritter
RUN adduser --disabled-password --ingroup processuswritter leonard \
    && passwd -u leonard

# copy user public ssh keys
COPY leonard/id_rsa.pub /leonard_id_rsa.pub
RUN mkdir -p /home/leonard/.ssh
RUN echo 'ssh-rsa ' >> /home/leonard/.ssh/authorized_keys
RUN cat /leonard_id_rsa.pub >> /home/leonard/.ssh/authorized_keys
RUN rm /leonard_id_rsa.pub

# create the processus exchange directory
RUN mkdir -p /var/sftp/processus
RUN chown -R root.processuswritter /var/sftp/processus
RUN chmod -R 770 /var/sftp/processus

# expose the default SSH port
EXPOSE 22

# starts the service in foreground mode (not as a daemon)
CMD [ "/usr/sbin/sshd", "-eD" ]