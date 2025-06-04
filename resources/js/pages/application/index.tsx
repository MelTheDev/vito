import { usePage } from '@inertiajs/react';
import { Site } from '@/types/site';
import React from 'react';
import AppWithDeployment from '@/pages/application/components/app-with-deployment';
import LoadBalancer from '@/pages/application/components/load-balancer';
import siteHelper from '@/lib/site-helper';

export default function Application() {
  const page = usePage<{
    site: Site;
  }>();

  siteHelper.storeSite(page.props.site);

  if (page.props.site.type === 'load-balancer') {
    return <LoadBalancer />;
  }

  return <AppWithDeployment />;
}
