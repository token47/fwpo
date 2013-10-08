# fwpo

## Firewall Port Opener

A remote web interface for opening netfilter rules for a limited time on linux.

By Andre Ruiz <andre.ruiz at gmail.com>

All files are distributed under the GPL2 license.

WARNING: Ugly code!! I'm not used to PHP, this is a survival hack.
         Don't blame me. If you make it better, send it to me.

Tested only in Linux + iptables + apache

This is a simple script to execute pre-determined rules inside your
firewall. Put it on a webserver on the machine running the firewall (if
you can't run a webserve there, this script is not for you), give sudo
the right permissions to run the commands without passwords, adjust the
rules to suit your needs and you are done. It's intended for hosts
with local firewall rules, not for firewalls per se.

In your normal firewall setup, leave closed the ports you will open
through this script. When you need to open one of them, open your
browser (this can be automated with scripts on the client), access the
URL of this script, open the port you want. The port will remain opened
for just some seconds, and just for the IP address you are on in that
moment, then it will close automaticaly. If you set up your firewall
correctly, the opened session will be kept going (because of the
stateful firewall nature of netfilter).

## INSTALL

1. Put the script somewhere in the htdocs tree
1. Adjust it to run only with SSL/HTTPS (optional)
1. Configure password using .htaccess in that dir
1. Edit the script and adjust the rules (sorry, no config files yet!)
1. Add permission for apache user to run iptables command without
   password in /etc/sudoers (see security concerns for tips and examples)
1. Adjust your old firewall rules to jump to the new chain you just
   invented to insert the temporary rules (and as well create the empty
   chain when loading up) (optional - you may insert them right in the
   main chain)
1. Test it! And send me comments :-)

## SUDO AND FIREWALL EXAMPLE CONFIGURATIONS

For the more paranoid, you can add as many explicit rules as you need
(unfortunately sudo does not use regular expressions, they are simple
shell expressions, but this should be enough):

```
apache  ALL=(ALL) NOPASSWD: \
        /sbin/iptables -[AD] INPUT_TMP -j ACCEPT -p tcp --dport ssh -s *,\
        /sbin/iptables -[AD] INPUT_TMP -j ACCEPT -p tcp --dport imap -s *
```

Or, alternatively, less secure (but you do not need to edit sudoers when
you change a rule in the script:

```
apache  ALL=(ALL) NOPASSWD: /sbin/iptables -[AD] INPUTTMP *
```

Or, even easier (in case you do not have a special chain):

```
apache  ALL=(ALL) NOPASSWD: /sbin/iptables -[AD] *
```

In my firewall script, somewhere in the middle of it, I have:

```
$IPTABLES -N INPUTTMP
$IPTABLES -A INPUT -j INPUTTMP
```

Just that. No rules, but an empty chain, that will be used later. This is
usable in the case you have DROPs later and want your temporary rules to
be inserted (read evaluated) in that specific "position" among others.

## SECURITY CONCERNS

- If you are a security paranoid, *do not use this script*
- If possible, use SSL to log on and access this script.
- Do not rely on this for security. This will only make the ports remain
  closed most of the time, but you should have enough confidence that
  those services would be ok if wide open all the time. I had ssh opened
  for much time without problems. Now it's closed and I use this script
  to open it on demand, just for me. If someone gets the apache
  password and opens it to himself, I'll be no worse than I was before,
  that was an already good situation (i.e. using keys only).
- You can set individual rules on sudo to match each rule the script
  will run (using a wildcard to the host IP) so no other rules will be
  ran even someone gets apache user control. In the not so paranoid
  case you just let apache user run iptables executable. Your choice.
- Instead of inserting rules any place in the netfilter chains (or
  inserting them in the main chain), I suggest you create a special
  chain that is empty all the time, and the main firewall chains could
  jump to it at some point inside the stardard rules. This script should
  only insert it's rules there (and this can be forced in sudo as well).
  It will help to narrow the possibilities to misuse (although not much)
  and will keep you organized if you already have a lot of rules
  running.

## FEEDBACK

This is a hack I made for a specific need, and don't use it anymore. But
please send any comments or pull requests you may have so I can improve it
in case anyone needs it.

