TextPress Openshift Quickstarts
===============================

### TextPress Development

[shameerc/TextPress](https://github.com/shameerc/TextPress) - [TextPress](http://textpress.shameerc.com)

This git repository helps you get up and running quickly w/ a TextPress installation
on OpenShift.

Running on OpenShift
--------------------

Create an account at http://openshift.redhat.com/ and install the client tools (run 'rhc setup' first)

Create a php-5.3 application (you can call your application whatever you want)

```shell
    rhc app create textpress php-5.3 --from-code=git://github.com/tigefa4u/TextPress-Openshift.git
```

That's it, you can now checkout your application at:

```
    http://textpress-$yournamespace.rhcloud.com
```

## TextPress

[shameerc/TextPress](https://github.com/shameerc/TextPress) - [TextPress](http://textpress.shameerc.com)

Thank you for visiting TextPress. It is a simple, easy to use PHP flat-file blog engine built on top of Slim Framework [Slim Framework](http://slimframework.com) released under the MIT public license.
