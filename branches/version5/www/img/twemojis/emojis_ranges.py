#! /usr/bin/env python


""" Prinmt the ranges of hexadecimal values found inm *.png
    Used to build the PHO funcion mb_encode_numericentity()
"""

import glob
import os

hexs = []

def print_line(first, last):
		mask = 'f' * (len(hex(last))-2)
		print "0x%x, 0x%x, 0, 0x%s," % (first, last, mask)

for f in glob.glob("*.png"):
	name, ext = os.path.splitext(f)

	name = name.replace("-", "")
	try:
		number = int(name, 16)
	except ValueError:
		continue

	hexs.append(number)


hexs = sorted(hexs)
first = current = previous = None
current = None
for h in hexs:
	if not first:
		first = previous = h
		continue

	if previous + 1 != h:
		print_line(first, previous)
		first = h

	previous = h

print_line(first, previous)

