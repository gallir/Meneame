#! /usr/bin/env python


""" Prinmt the ranges of hexadecimal values found inm *.png
    Used to build the PHO funcion mb_encode_numericentity()
"""

import glob
import os

hexs = []
doubles = []

def print_line(first, last):
		mask = 'f' * (len(hex(last))-2)
		print "\t\t0x%x, 0x%x, 0, 0x%s," % (first, last, mask)

def get_ranges(hexs):
	tmp = []


	for x in hexs:
		try:
			number = int(x, 16)
		except ValueError:
			continue
		tmp.append(number)

	tmp = sorted(tmp)
	first = current = previous = None
	current = None
	for h in tmp:
		if not first:
			first = previous = h
			continue
		if previous and previous == h:
			continue

		if previous + 1 != h:
			print_line(first, previous)
			first = h

		previous = h

	print_line(first, previous)



for f in glob.glob("*.png"):
	name, ext = os.path.splitext(f)

	codes = name.split("-", 2)

	if len(codes) > 1:
		doubles.append(codes)
		continue

	hexs.append(codes[0])


print("/* Generated automatically by emojis_ranges.py */");
print("\t static $map = array(")
get_ranges(hexs)
print("\t);")

print("/* Regexes for double unicodes */");
print("\t static $regexes = array(")
doubles = sorted(doubles)
for codes in doubles:
	print ("\t\t'/\\x{%s}\\x{%s}/u' => ' {0x%s-%s} '," % (codes[0], codes[1], codes[0], codes[1]))
print("\t);")






