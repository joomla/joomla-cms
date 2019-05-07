#!/usr/bin/env bash

JOOMLA_BASE=$1
HEADER=$(cat <<'EOF'
#
#
#            JJJJJJJJJJJ                                                       lllllll
#            J:::::::::J                                                       l:::::l
#            J:::::::::J                                                       l:::::l
#            JJ:::::::JJ                                                       l:::::l
#              J:::::J   ooooooooooo     ooooooooooo      mmmmmmm    mmmmmmm    l::::l   aaaaaaaaaaaaa
#              J:::::J oo:::::::::::oo oo:::::::::::oo  mm:::::::m  m:::::::mm  l::::l   a::::::::::::a
#              J:::::Jo:::::::::::::::o:::::::::::::::om::::::::::mmatias:::::m l::::l   aaaaaaaaa:::::a
#              J:::::jo:::::ooooo:::::o:::::ooooo:::::om::::::::::::::::::::::m l::::l            a::::a
#              J:::::Jo::::o     o::::o::::o     o::::om:::::mmm::::::mmm:::::m l::::l     aaaaaaa:::::a
#  JJJJJJJ     J:::::Jo::::o     o::::o::::o     o::::om::::m   m::::m   m::::m l::::l   aallon:::::::a
#  J:::::J     J:::::Jo::::o     o::::o::::o     o::::om::::m   m::::m   m::::m l::::l  a::::aaaa::::::a
#  J::::::J   J::::::Jo::::o     o::::o::::o     o::::om::::m   m::::m   m::::m l::::l a::::a    a:::::a
#  J:::::::JJJ:::::::Jo:::::ooooo:::::o:::::ooooo:::::om::::m   m::::m   m::::ml::::::la::::a    a:::::a
#   JJ:::::::::::::JJ o:::::::::::::::o:::::::::::::::om::::m   m::::m   m::::ml::::::la:::::aaaa::::::a
#     JJ:::::::::JJ    oo:::::::::::oo oo:::::::::::oo m::::m   m::::m   m::::ml::::::l andrei::::aa:::a
#       JJJJJJJJJ        ooooooooooo     ooooooooooo   mmmmmm   mmmmmm   mmmmmmllllllll  aaaaaaaaaa  aaaa
#
#

#                 AAA                                    iiii
#                A:::A                                  i::::i
#               A:::::A                                  iiii
#              A:::::::A
#             A:::::::::A          ppppp   ppppppppp   iiiiiii
#            A:::::A:::::A         p::::ppp:::::::::p  i:::::i
#           A:::::A A:::::A        p:::::::::::::::::p  i::::i
#          A:::::A   A:::::A       pp::::::ppppp::::::p i::::i
#         A:::::A     A:::::A       p:::::p     p:::::p i::::i
#        A:::::AAAAAAAAA:::::A      p:::::p     p:::::p i::::i
#       A:::::::::::::::::::::A     p:::::p     p:::::p i::::i
#      A:::::AAAAAAAAAAAAA:::::A    p:::::p    p::::::p i::::i
#     A:::::A             A:::::A   p:::::ppppp:::::::pi::::::i
#    A:::::A               A:::::A  p::::::::::::::::p i::::::i
#   A:::::A                 A:::::A Puneet::::::::pp  i::::::i
#  AAAAAAA                   AAAAAAAp::::::pppppppp    iiiiiiii
#                                   p:::::p
#                                   p:::::p
#                                  p:::::::p
#                                  p:::::::p
#                                  p:::::::p
#                                  ppppppppp
#

#  TTTTTTTTTTTTTTTTTTTTTTTEEEEEEEEEEEEEEEEEEEEEE   SSSSSSSSSSSSSSS TTTTTTTTTTTTTTTTTTTTTTTIIIIIIIIIINNNNNNNN        NNNNNNNN        GGGGGGGGGGGGG
#  T:::::::::::::::::::::TE::::::::::::::::::::E SS:::::::::::::::ST:::::::::::::::::::::TI::::::::IN:::::::N       N::::::N     GGG::::::::::::G
#  T:::::::::::::::::::::TE::::::::::::::::::::ES:::::SSSSSS::::::ST:::::::::::::::::::::TI::::::::IN::::::::N      N::::::N   GG:::::::::::::::G
#  T:::::TT:::::::TT:::::TEE::::::EEEEEEEEE::::ES:::::S     SSSSSSST:::::TT:::::::TT:::::TII::::::IIN:::::::::N     N::::::N  G:::::GGGGGGGG::::G
#  TTTTTT  T:::::T  TTTTTT  E:::::E       EEEEEES:::::S            TTTTTT  T:::::T  TTTTTT  I::::I  N::::::::::N    N::::::N G:::::G       GGGGGG
#          T:::::T          E:::::E             S:::::S                    T:::::T          I::::I  N:::::::::::N   N::::::NG:::::G
#          T:::::T          E::::::EEEEEEEEEE    S::::SSSS                 T:::::T          I::::I  N:::::::N::::N  N::::::NG:::::G
#          T:::::T          E:::::::::::::::E     SS::::::SSSSS            T:::::T          I::::I  N::::::N N::::N N::::::NG:::::G    GGGGGGGGGG
#          T:::::T          E:::::::::::::::E       SSS::::::::SS          T:::::T          I::::I  N::::::N  N::::N:::::::NG:::::G    G::::::::G
#          T:::::T          E::::::EEEEEEEEEE          SSSSSS::::S         T:::::T          I::::I  N::::::N   N:::::::::::NG:::::G    GGGGG::::G
#          T:::::T          E:::::E                         S:::::S        T:::::T          I::::I  N::::::N    N::::::::::NG:::::G        G::::G
#          T:::::T          E:::::E       EEEEEE            S:::::S        T:::::T          I::::I  N::::::N     N:::::::::N G:::::G       G::::G
#        TT:::::::TT      EE::::::EEEEEEEE:::::ESSSSSSS     S:::::S      TT:::::::TT      II::::::IIN::::::N      N::::::::N  G:::::GGGGGGGG::::G
#        T:::::::::T      E::::::::::::::::::::ES::::::SSSSSS:::::S      Tobias::::T      I::::::::IN::::::N       Niels:::N   GGeorge::::::::::G
#        T:::::::::T      E::::::::::::::::::::ESandra::::::::::SS       T:::::::::T      I::::::::IN::::::N        N::::::N     GGG::::::GGG:::G
#        TTTTTTTTTTT      EEEEEEEEEEEEEEEEEEEEEE SSSSSSSSSSSSSSS         TTTTTTTTTTT      IIIIIIIIIINNNNNNNN         NNNNNNN        GGGGGG   GGGG
#
#
EOF
)

tput setaf 2 -T xterm
echo "-------------------------------"
echo -e "${HEADER}"
echo "-------------------------------"
tput sgr0 -T xterm

# Switch to Joomla base directory
cd $JOOMLA_BASE

# Install Joomla
apache2ctl -D FOREGROUND &
google-chrome --version
chmod 755 libraries/vendor/joomla-projects/selenium-server-standalone/bin/webdrivers/chrome/linux/chromedriver
cp tests/Codeception/acceptance.suite.dist.yml tests/Codeception/acceptance.suite.yml
libraries/vendor/bin/robo run:install

# Setting up api tests
cp tests/Api/codeception.yml codeception.yml
libraries/vendor/bin/codecept run api
