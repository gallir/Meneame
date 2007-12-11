<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/** Zend_Search_Lucene_Index_SegmentWriter_DocumentWriter */
require_once 'Zend/Search/Lucene/Index/SegmentWriter/DocumentWriter.php';

/** Zend_Search_Lucene_Index_SegmentInfo */
require_once 'Zend/Search/Lucene/Index/SegmentInfo.php';

/** Zend_Search_Lucene_Index_SegmentMerger */
require_once 'Zend/Search/Lucene/Index/SegmentMerger.php';



/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Index_Writer
{
    /**
     * @todo Implement Analyzer substitution
     * @todo Implement Zend_Search_Lucene_Storage_DirectoryRAM and Zend_Search_Lucene_Storage_FileRAM to use it for
     *       temporary index files
     * @todo Directory lock processing
     */

    /**
     * Number of documents required before the buffered in-memory
     * documents are written into a new Segment
     *
     * Default value is 10
     *
     * @var integer
     */
    public $maxBufferedDocs = 10;

    /**
     * Largest number of documents ever merged by addDocument().
     * Small values (e.g., less than 10,000) are best for interactive indexing,
     * as this limits the length of pauses while indexing to a few seconds.
     * Larger values are best for batched indexing and speedier searches.
     *
     * Default value is PHP_INT_MAX
     *
     * @var integer
     */
    public $maxMergeDocs = PHP_INT_MAX;

    /**
     * Determines how often segment indices are merged by addDocument().
     *
     * With smaller values, less RAM is used while indexing,
     * and searches on unoptimized indices are faster,
     * but indexing speed is slower.
     *
     * With larger values, more RAM is used during indexing,
     * and while searches on unoptimized indices are slower,
     * indexing is faster.
     *
     * Thus larger values (> 10) are best for batch index creation,
     * and smaller values (< 10) for indices that are interactively maintained.
     *
     * Default value is 10
     *
     * @var integer
     */
    public $mergeFactor = 10;

    /**
     * File system adapter.
     *
     * @var Zend_Search_Lucene_Storage_Directory
     */
    private $_directory = null;


    /**
     * Changes counter.
     *
     * @var integer
     */
    private $_versionUpdate = 0;

    /**
     * List of the segments, created by index writer
     * Array of Zend_Search_Lucene_Index_SegmentInfo objects
     *
     * @var array
     */
    private $_newSegments = array();

    /**
     * List of segments to be deleted on commit
     *
     * @var array
     */
    private $_segmentsToDelete = array();

    /**
     * Current segment to add documents
     *
     * @var Zend_Search_Lucene_Index_SegmentWriter_DocumentWriter
     */
    private $_currentSegment = null;

    /**
     * Array of Zend_Search_Lucene_Index_SegmentInfo objects for this index.
     *
     * It's a reference to the corresponding Zend_Search_Lucene::$_segmentInfos array
     *
     * @var array Zend_Search_Lucene_Index_SegmentInfo
     */
    private $_segmentInfos;

    /**
     * List of indexfiles extensions
     *
     * @var array
     */
    private static $_indexExtensions = array('.cfs' => '.cfs',
                                             '.fnm' => '.fnm',
                                             '.fdx' => '.fdx',
                                             '.fdt' => '.fdt',
                                             '.tis' => '.tis',
                                             '.tii' => '.tii',
                                             '.frq' => '.frq',
                                             '.prx' => '.prx',
                                             '.tvx' => '.tvx',
                                             '.tvd' => '.tvd',
                                             '.tvf' => '.tvf',
                                             '.del' => '.del',
                                             '.sti' => '.sti' );

    /**
     * Create empty index
     *
     * @param Zend_Search_Lucene_Storage_Directory $directory
     * @param integer $generation
     * @param integer $nameCount
     */
    public static function createIndex(Zend_Search_Lucene_Storage_Directory $directory, $generation, $nameCount)
    {
        if ($generation == 0) {
            // Create index in pre-2.1 mode

            foreach ($directory->fileList() as $file) {
                if ($file == 'deletable' ||
                    $file == 'segments'  ||
                    isset(self::$_indexExtensions[ substr($file, strlen($file)-4)]) ||
                    preg_match('/\.f\d+$/i', $file) /* matches <segment_name>.f<decimal_nmber> file names */) {
                        $directory->deleteFile($file);
                    }
            }

            $segmentsFile = $directory->createFile('segments');
            $segmentsFile->writeInt((int)0xFFFFFFFF);

            // write version (is initialized by current time
            // $segmentsFile->writeLong((int)microtime(true));
            $version = microtime(true);
            $segmentsFile->writeInt((int)($version/((double)0xFFFFFFFF + 1)));
            $segmentsFile->writeInt((int)($version & 0xFFFFFFFF));

            // write name counter
            $segmentsFile->writeInt($nameCount);
            // write segment counter
            $segmentsFile->writeInt(0);

            $deletableFile = $directory->createFile('deletable');
            // write counter
            $deletableFile->writeInt(0);
        } else {
            $genFile = $directory->createFile('segments.gen');

            $genFile->writeInt((int)0xFFFFFFFE);
            // Write generation two times
            $genFile->writeLong($generation);
            $genFile->writeLong($generation);

            $segmentsFile = $directory->createFile(Zend_Search_Lucene::getSegmentFileName($generation));
            $segmentsFile->writeInt((int)0xFFFFFFFD);

            // write version (is initialized by current time
            // $segmentsFile->writeLong((int)microtime(true));
            $version = microtime(true);
            $segmentsFile->writeInt((int)($version/((double)0xFFFFFFFF + 1)));
            $segmentsFile->writeInt((int)($version & 0xFFFFFFFF));

            // write name counter
            $segmentsFile->writeInt($nameCount);
            // write segment counter
            $segmentsFile->writeInt(0);
        }
    }

    /**
     * Open the index for writing
     *
     * IndexWriter constructor needs Directory as a parameter. It should be
     * a string with a path to the index folder or a Directory object.
     * Second constructor parameter create is optional - true to create the
     * index or overwrite the existing one.
     *
     * @param Zend_Search_Lucene_Storage_Directory $directory
     * @param array $segmentInfos
     * @param boolean $create
     */
    public function __construct(Zend_Search_Lucene_Storage_Directory $directory, &$segmentInfos)
    {
        $this->_directory    = $directory;
        $this->_segmentInfos = &$segmentInfos;
    }

    /**
     * Adds a document to this index.
     *
     * @param Zend_Search_Lucene_Document $document
     */
    public function addDocument(Zend_Search_Lucene_Document $document)
    {
        if ($this->_currentSegment === null) {
            $this->_currentSegment =
                new Zend_Search_Lucene_Index_SegmentWriter_DocumentWriter($this->_directory, $this->_newSegmentName());
        }
        $this->_currentSegment->addDocument($document);

        if ($this->_currentSegment->count() >= $this->maxBufferedDocs) {
            $this->commit();
        }

        $this->_versionUpdate++;

        $this->_maybeMergeSegments();
    }


    /**
     * Merge segments if necessary
     */
    private function _maybeMergeSegments()
    {
        $segmentSizes = array();
        foreach ($this->_segmentInfos as $segName => $segmentInfo) {
            $segmentSizes[$segName] = $segmentInfo->count();
        }

        $mergePool   = array();
        $poolSize    = 0;
        $sizeToMerge = $this->maxBufferedDocs;
        asort($segmentSizes, SORT_NUMERIC);
        foreach ($segmentSizes as $segName => $size) {
            // Check, if segment comes into a new merging block
            while ($size >= $sizeToMerge) {
                // Merge previous block if it's large enough
                if ($poolSize >= $sizeToMerge) {
                    $this->_mergeSegments($mergePool);
                }
                $mergePool   = array();
                $poolSize    = 0;

                $sizeToMerge *= $this->mergeFactor;

                if ($sizeToMerge > $this->maxMergeDocs) {
                    return;
                }
            }

            $mergePool[] = $this->_segmentInfos[$segName];
            $poolSize += $size;
        }

        if ($poolSize >= $sizeToMerge) {
            $this->_mergeSegments($mergePool);
        }
    }

    /**
     * Merge specified segments
     *
     * $segments is an array of SegmentInfo objects
     *
     * @param array $segments
     */
    private function _mergeSegments($segments)
    {
        // Try to get exclusive non-blocking lock to the 'index.optimization.lock'
        // Skip optimization if it's performed by other process right now
        $optimizationLock = $this->_directory->createFile('optimization.lock');
        if (!$optimizationLock->lock(LOCK_EX,true)) {
            return;
        }

        $newName = $this->_newSegmentName();
        $merger = new Zend_Search_Lucene_Index_SegmentMerger($this->_directory,
                                                             $newName);
        foreach ($segments as $segmentInfo) {
            $merger->addSource($segmentInfo);
            $this->_segmentsToDelete[$segmentInfo->getName()] = $segmentInfo->getName();
        }

        $newSegment = $merger->merge();
        if ($newSegment !== null) {
            $this->_newSegments[$newSegment->getName()] = $newSegment;
        }

        $this->commit();

        // optimization is finished
        $optimizationLock->unlock();
    }

    /**
     * Update segments file by adding current segment to a list
     *
     * @throws Zend_Search_Lucene_Exception
     */
    private function _updateSegments()
    {
        // Get an exclusive index lock
        $lock = Zend_Search_Lucene::obtainWriteLock($this->_directory);

        $generation = Zend_Search_Lucene::getActualGeneration($this->_directory);
        $segmentsFile   = $this->_directory->getFileObject(Zend_Search_Lucene::getSegmentFileName($generation), false);
        $newSegmentFile = $this->_directory->createFile(Zend_Search_Lucene::getSegmentFileName(++$generation), false);

        try {
        	$genFile = $this->_directory->getFileObject('segments.gen', false);
        } catch (Zend_Search_Lucene_Exception $e) {
            if (strpos($e->getMessage(), 'is not readable') !== false) {
            	$genFile = $this->_directory->createFile('segments.gen');
            } else {
            	throw $e;
            }
        }
        
        $genFile->writeInt((int)0xFFFFFFFE);
        // Write generation (first copy)
        $genFile->writeLong($generation);

        try {
	        // Write format marker
	        $newSegmentFile->writeInt((int)0xFFFFFFFD);
	
	        // Skip format identifier
	        $segmentsFile->seek(4, SEEK_CUR);
	        // $version = $segmentsFile->readLong() + $this->_versionUpdate;
	        // Process version on 32-bit platforms
	        $versionHigh = $segmentsFile->readInt();
	        $versionLow  = $segmentsFile->readInt();
	        $version = $versionHigh * ((double)0xFFFFFFFF + 1) +
	                   (($versionLow < 0)? (double)0xFFFFFFFF - (-1 - $versionLow) : $versionLow);
	        $version += $this->_versionUpdate;
	        $this->_versionUpdate = 0;
	        $newSegmentFile->writeInt((int)($version/((double)0xFFFFFFFF + 1)));
	        $newSegmentFile->writeInt((int)($version & 0xFFFFFFFF));
	
	        // Write segment name counter
	        $newSegmentFile->writeInt($segmentsFile->readInt());
	
	        // Get number of segments offset
	        $numOfSegmentsOffset = $newSegmentFile->tell();
	        // Write dummy data (segment counter)
	        $newSegmentFile->writeInt(0);
	
	        // Read number of segemnts
	        $segmentsCount = $segmentsFile->readInt();

	        $segments = array();
	        for ($count = 0; $count < $segmentsCount; $count++) {
	            $segName = $segmentsFile->readString();
	            $segSize = $segmentsFile->readInt();
	
	            if ($generation == 1 /* retrieved generation is 0 */) {
	                // pre-2.1 index format
	                $delGenHigh = 0;
	                $delGenLow  = 0;
	                $hasSingleNormFile = false;
	                $numField = (int)0xFFFFFFFF;
	                $isCompound = 1;
	            } else {
	                //$delGen          = $segmentsFile->readLong();
	                $delGenHigh        = $segmentsFile->readInt();
	                $delGenLow         = $segmentsFile->readInt();
	                $hasSingleNormFile = $segmentsFile->readByte();
	                $numField          = $segmentsFile->readInt();
	
	                $normGens = array();
	                if ($numField != (int)0xFFFFFFFF) {
	                    for ($count1 = 0; $count1 < $numField; $count1++) {
	                        $normGens[] = $segmentsFile->readLong();
	                    }
	                }
	                $isCompound        = $segmentsFile->readByte();
	            }
	
	            if (!in_array($segName, $this->_segmentsToDelete)) {
	            	// Load segment if necessary
	            	if (!isset($this->_segmentInfos[$segName])) {
	            		$delGen = $delGenHigh * ((double)0xFFFFFFFF + 1) +
	                   			  (($delGenLow < 0)? (double)0xFFFFFFFF - (-1 - $delGenLow) : $delGenLow);
	            		$this->_segmentInfos[$segName] = 
	                                new Zend_Search_Lucene_Index_SegmentInfo($this->_directory,
	                                                                         $segName,
	                                                                         $segSize,
	                                                                         $delGen,
	                                                                         $hasSingleNormFile,
	                                                                         $isCompound);
	            	} else {
	            		// Retrieve actual detetions file generation number
	            		$delGen = $this->_segmentInfos[$segName]->getDelGen();
	            		
	            		if ($delGen >= 0) {
		            		$delGenHigh = (int)($delGen/((double)0xFFFFFFFF + 1));
		            		$delGenLow  =(int)($delGen & 0xFFFFFFFF);
	            		} else {
	            			$delGenHigh = $delGenLow = (int)0xFFFFFFFF;
	            		}
	            	}
	            	
	                $newSegmentFile->writeString($segName);
	                $newSegmentFile->writeInt($segSize);
	                $newSegmentFile->writeInt($delGenHigh);
	                $newSegmentFile->writeInt($delGenLow);
	                $newSegmentFile->writeByte($hasSingleNormFile);
	                $newSegmentFile->writeInt($numField);
	                if ($numField != (int)0xFFFFFFFF) {
	                    foreach ($normGens as $normGen) {
	                        $newSegmentFile->writeLong($normGen);
	                    }
	                }
	                $newSegmentFile->writeByte($isCompound);
	
	                $segments[$segName] = $segSize;
	            }
	        }
	        $segmentsFile->close();
	
	        $segmentsCount = count($segments) + count($this->_newSegments);
	
	        foreach ($this->_newSegments as $segName => $segmentInfo) {
	            $newSegmentFile->writeString($segName);
	            $newSegmentFile->writeInt($segmentInfo->count());
	
	            // delete file generation: -1 (there is no delete file yet)
	            $newSegmentFile->writeInt((int)0xFFFFFFFF);$newSegmentFile->writeInt((int)0xFFFFFFFF);
	            // HasSingleNormFile
	            $newSegmentFile->writeByte($segmentInfo->hasSingleNormFile());
	            // NumField
	            $newSegmentFile->writeInt((int)0xFFFFFFFF);
	            // IsCompoundFile
	            $newSegmentFile->writeByte($segmentInfo->isCompound());
	
	            $segments[$segmentInfo->getName()] = $segmentInfo->count();
	            $this->_segmentInfos[$segName] = $segmentInfo;
	        }
	        $this->_newSegments = array();
	
	        $newSegmentFile->seek($numOfSegmentsOffset);
	        $newSegmentFile->writeInt($segmentsCount);  // Update segments count
	        $newSegmentFile->close();
	
	        // Clean-up directory
	        foreach ($this->_directory->fileList() as $file) {
	            if ($file == 'deletable' ||
	                $file == 'segments'  ||
	                isset(self::$_indexExtensions[substr($file, strlen($file)-4)]) ||
	                preg_match('/^segments_[a-zA-Z0-9]+$/i', $file) /* matches 'segments_xxx' file names */ ||
	                preg_match('/\.f\d+$/i', $file) /* matches <segment_name>.f<decimal_nmber> file names */) {
	                    // check, that file is not used by current index generation
	                    if ($file == Zend_Search_Lucene::getSegmentFileName($generation) ||
	                        isset($segments[substr($file, 0, strlen($file)-4)]) ||
	                        isset($segments[substr($file, 0, strpos($file, '.f'))]) ||
	                        substr($file, strlen($file)-4) == '.del') {
	                        continue;
	                    }
	
	                    try {
	                        $this->_directory->deleteFile($file);
	                    } catch (Zend_Search_Lucene_Exception $e) {
	                        if (strpos($e->getMessage(), 'Can\'t delete file') === 0) {
	                            // File is under processing
	                            // Stop clean-up process
	                            break;
	                        } else {
	                            throw $e;
	                        }
	                    }
	                }
	        }
        } catch (Exception $e) {
        	/** Restore previous index generation */
        	$generation--;
        	$genFile->seek(4, SEEK_SET);
        	// Write generation number twice
        	$genFile->writeLong($generation); $genFile->writeLong($generation);

        	// Release index write lock
            Zend_Search_Lucene::releaseWriteLock($this->_directory, $lock);
            
            // Throw the exception
            throw $e;
        }

        // Write generation (second copy)
        $genFile->writeLong($generation);
        
        // Release index write lock
        Zend_Search_Lucene::releaseWriteLock($this->_directory, $lock);

        // Remove unused segments from segments list
        foreach ($this->_segmentInfos as $segName => $segmentInfo) {
            if (!isset($segments[$segName])) {
                unset($this->_segmentInfos[$segName]);
            }
        }
    }

    /**
     * Commit current changes
     */
    public function commit()
    {
        if ($this->_currentSegment !== null) {
            $newSegment = $this->_currentSegment->close();
            if ($newSegment !== null) {
                $this->_newSegments[$newSegment->getName()] = $newSegment;
            }
            $this->_currentSegment = null;
        }

        $this->_updateSegments();
    }


    /**
     * Merges the provided indexes into this index.
     *
     * @param array $readers
     * @return void
     */
    public function addIndexes($readers)
    {
        /**
         * @todo implementation
         */
    }

    /**
     * Merges all segments together into a single segment, optimizing
     * an index for search.
     * Input is an array of Zend_Search_Lucene_Index_SegmentInfo objects
     *
     * @throws Zend_Search_Lucene_Exception
     */
    public function optimize()
    {
        $this->_mergeSegments($this->_segmentInfos);
    }

    /**
     * Get name for new segment
     *
     * @return string
     */
    private function _newSegmentName()
    {
        $lock = Zend_Search_Lucene::obtainWriteLock($this->_directory);
    	
        $generation = Zend_Search_Lucene::getActualGeneration($this->_directory);
        $segmentsFile = $this->_directory->getFileObject(Zend_Search_Lucene::getSegmentFileName($generation), false);

        $segmentsFile->seek(12); // 12 = 4 (int, file format marker) + 8 (long, index version)
        $segmentNameCounter = $segmentsFile->readInt();

        $segmentsFile->seek(12); // 12 = 4 (int, file format marker) + 8 (long, index version)
        $segmentsFile->writeInt($segmentNameCounter + 1);

        // Flash output to guarantee that wrong value will not be loaded between unlock and
        // return (which calls $segmentsFile destructor)
        $segmentsFile->flush();

        Zend_Search_Lucene::releaseWriteLock($this->_directory, $lock);
        
        return '_' . base_convert($segmentNameCounter, 10, 36);
    }

}
