#!/usr/bin/perl

##
# Checks XP classes for conformance to coding standards
#
# Put into CVSROOT/commitinfo as follows:
#   ALL /path/to/xpcsc.pl
#
# $Id$

use constant ETAB       => "ETAB";
use constant EINDENT    => "EINDENT";
use constant ECOMMENT   => "ECOMMENT";
use constant ENOHEADER  => "ENOHEADER";
use constant ESHORTOPEN => "ESHORTOPEN";

use constant WTBD       => "WTBD";
use constant WOUTPUT    => "WOUTPUT";

use constant WNOHINT    => "WNOHINT";

%LINK = (
  # Errors
  ETAB        => "http://xp-framework.net/devel/coding.html#5",
  EINDENT     => "http://xp-framework.net/devel/coding.html#5",
  ECOMMENT    => "http://xp-framework.net/devel/coding.html#9",
  ENOHEADER   => "http://xp-framework.net/devel/coding.html#2",
  ESHORTOPEN  => "http://xp-framework.net/devel/coding.html#3",

  # Warnings
  WTBD        => "http://xp-framework.net/devel/coding.html#13",
  WOUTPUT     => "n/a",
  WNOHINT     => "n/a",
);

# {{{ utility functions for mail notify
sub getRealname {
  my $sysname= shift;
  open (UDB, "/etc/passwd");
  my @lines= <UDB>;
  my $line;
  close (UDB);

  my $realname= 'Mister Booombastic';
  foreach $line (@lines) {
    my ($uname, $pass, $uid, $id, $info, $home, $shell)= split /:/, $line;
    if ($sysname eq $uname) {
      $realname= $info;
      $realname =~ s/,.*$//g;
    }
  }

  return $realname;
}

sub trim {
  my ($x) = @_;
  $x =~ s/^\s+//;
  $x =~ s/\s+$//;
  return $x;
}

sub getEmail {
  my $sysname= shift;
  my $username= lc(getRealname ($sysname));

  $username =~ s/\ /\./g;
  $username =~ s/�/ae/g;
  $username =~ s/�/oe/g;
  $username =~ s/�/ue/g;
  $username =~ s/�/ss/g;
  return $username.'@php3.de';
}
# }}}

# {{{ void error (string message, string code)
sub error() {
  my $message= shift;
  my $code= shift;
  
  $_ =~ s/\t/\\t/g;
  chomp $_;
  my $out= "*** Error: ".$message." at line ".$l." of ".$FILE."\n    ".$_."\n---> [".$code."] ".$LINK{$code}."\n";
  print $out;

  open (SENDMAIL, "| /usr/sbin/sendmail -t");
  print SENDMAIL "To: friebe\@php3.de, kiesel\@php3.de\n";
  print SENDMAIL "From: \"".getRealname ($ENV{'USER'})."\" <".getEmail ($ENV{'USER'}).">\n";
  print SENDMAIL "Reply-To: $to\n";
  print SENDMAIL "Subject: [CVS] commit failure\n";
  print SENDMAIL "X-CVS: ".$ENV{'CVSROOT'}."\n";
  print SENDMAIL "\n";
  print SENDMAIL $out;
  close (SENDMAIL);
  
  close FILE;
  exit 32;
}
# }}}

# {{{ void warning (string message, string code)
sub warning() {
  my $message= shift;
  my $code= shift;
  
  $_ =~ s/\t/\\t/g;
  chomp $_;
  my $out= "--- Warning: ".$message." at line ".$l." of ".$FILE."\n    ".$_."\n---> [".$code."] ".$LINK{$code}."\n";
  print $out;
  $warnings++;
}
# }}}

# {{{ main
while (@ARGV) {
  $FILE= shift @ARGV;
  $warnings= 0;
  
  if (!-f $FILE || $FILE !~ /\.class\.php$/) { next; }
  
  open(FILE, $FILE) || die "Cannot open $FILE";
  $l= 0;
  while (<FILE>) {
    $l++;

    SWITCH: {
      if (1 == $l && $_ !~ /^\<\?php/) { &error("First line does not contain <?php", ESHORTOPEN); }
      if (2 == $l && $_ !~ /^\/\*/) { &error("Second line does not contain XP header", ENOHEADER); }
      if (4 == $l && $_ !~ /\$Id/) { &error("Second line does not contain CVS Id-Tag", ENOHEADER); }
      if ($l < 5) { next; }
    }

    if ($_ =~ /\t/) {
      &error("Tab character found", ETAB);
    }

    if ($_ =~ /^(\s+)class/ && 2 != length($1)) {
      &error("Class declarations must be indented with 2 spaces", EINDENT);
    }

    if ($_ =~ /^(\s+)function/ && 4 != length($1)) {
      &error("Methods must be indented with 4 spaces", EINDENT);
    }

    if ($_ =~ /(.)\/\*[^\*]/ && $l > 2 && $1 ne "'") {
      &error("Block comments may not be contained within source, use // instead", ECOMMENT);
    }

    if ($_ =~ /(.*)(echo|var_dump|print_r)/ && $1 !~ /\*/) {
      &warning("You should not be using direct output statements ($2)", WOUTPUT);
    }

    if ($_ =~ /(TODO|TBI|TBD|FIXME)/) {
      &warning("You have a $1 comment in your sourcecode...", WTBD);
    }
    
    if ($_ =~ /\@(access|param|return|throws)\s+$/) {
      &warning("Your inline documentation is incomplete.", WNOHINT);
    }
    
    if ($_ =~ /\(Insert method's description here\)/) {
      &warning("You should supply a description for your method", WNOHINT);
    }
  }
  close FILE;

  print $FILE." coding standards conformance ok [".$warnings." warning(s)]\n";
}
# }}}
