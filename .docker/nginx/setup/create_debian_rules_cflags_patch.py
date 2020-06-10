#!/usr/bin/env python3
"""
Create the patch file based on "nginx-source/debian/rules" by changing CFLAGS (configure flags) for the "nginx" and the "nginx-dbg" packages
"""

import argparse
import os
import re
import sys


def ansi_format(string, color):
    fg_colors = {
        'black': 30,
        'red': 31,
        'green': 32,
        'yellow': 33,
        'blue': 34,
        'purple': 35,
        'cyan': 36,
        'grey': 37
    }
    fg_color = str(fg_colors[color])

    print(f'\033[0m\033[0{fg_color}m{string}\033[0m')


def type_writable_file(file):
    if not os.path.isfile(file):
        raise argparse.ArgumentTypeError(f'The "{file}" file does not exists')
    if not os.access(file, os.W_OK):
        raise argparse.ArgumentTypeError(f'The "{file}" file is not writable')

    return file


def get_parser():
    parser = argparse.ArgumentParser(description=__doc__,
                                     formatter_class=lambda prog: argparse.
                                     RawTextHelpFormatter(prog, width=85))
    parser.add_argument('--debian-rules-file',
                        type=type_writable_file,
                        required=True,
                        help='Path to the "nginx-source/debian/rules" file')
    parser.add_argument(
        '--nginx-cflags',
        required=True,
        help='CFLAGS (configure flags) for the "nginx" package')
    parser.add_argument(
        '--nginx-dbg-cflags',
        required=True,
        help='CFLAGS (configure flags) for the "nginx-dbg" package')

    return parser


def main(script_name, args):
    options = get_parser().parse_args(args)
    debian_rules_file = options.debian_rules_file
    nginx_cflags = options.nginx_cflags
    nginx_dbg_cflags = options.nginx_dbg_cflags

    script_args = ' '.join(args)
    command = f'{script_name} {script_args}'
    ansi_format(command, 'yellow')

    debian_rules_basedir = os.path.dirname(debian_rules_file)
    with open(debian_rules_file, 'r') as handle:
        content = handle.read()

        pattern = r'(cd \$\(BUILDDIR_nginx\) && \\)(.*?)(touch \$@)'
        replacement = fr'\1\n{nginx_cflags}\n\t\3'
        new_content = re.sub(pattern, replacement, content, 0, re.DOTALL)

        pattern = r'(cd \$\(BUILDDIR_nginx_debug\) && \\)(.*?)(touch \$@)'
        replacement = fr'\1\n{nginx_dbg_cflags}\n\t\3'
        new_content = re.sub(pattern, replacement, new_content, 0, re.DOTALL)

        new_debian_rules_file = os.path.join(debian_rules_basedir,
                                             'rules-cflags.new')
        with open(new_debian_rules_file, 'w') as new_debian_rules_handle:
            new_debian_rules_handle.write(new_content)

        patch_file = os.path.join(debian_rules_basedir, 'rules-cflags.patch')
        patch_command = f'diff --unified {debian_rules_file} {new_debian_rules_file} | tee {patch_file}'
        os.system(patch_command)

        output = f'''
The patch file: {patch_file}
To apply the patch: patch --backup {debian_rules_file} {patch_file}
        '''
        ansi_format(output, 'green')


if __name__ == '__main__':
    main(sys.argv[0], sys.argv[1:])
