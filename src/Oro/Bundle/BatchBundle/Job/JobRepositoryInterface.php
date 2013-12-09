<?php

namespace Oro\Bundle\BatchBundle\Job;

use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Common interface for Job repositories which should handle how job are stored, updated
 * and retrieved
 *
 * Inspired by Spring Batch org.springframework.batch.core.repository.JobRepository;
 *
 */
interface JobRepositoryInterface
{
    /**
     * Create a JobExecution object
     *
     * @param JobInstance $job
     *
     * @return JobExecution
     */
    public function createJobExecution(JobInstance $job);

    /**
     * Update a JobExecution
     *
     * @param JobExecution $jobExecution
     *
     * @return JobExecution
     */
    public function updateJobExecution(JobExecution $job);

    /**
     * Update a StepExecution
     *
     * @param StepExecution $stepExecution
     *
     * @return StepExecution
     */
    public function updateStepExecution(StepExecution $job);
}
